<?php

declare(strict_types=1);

namespace app\commands;

use app\models\Author;
use app\services\catalog\OpenLibraryClient;
use app\services\storage\FileStorageInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use yii\console\Controller;
use yii\console\ExitCode;

final class AuthorProfileController extends Controller
{
    public int $limit = 400;
    public int $concurrency = 8;

    private const MIN_PHOTO_BYTES = 1500;

    public function options($actionID): array
    {
        return ['limit', 'concurrency'];
    }

    public function actionIndex(): int
    {
        $client = new OpenLibraryClient(\Yii::$container->get(ClientInterface::class));

        $authors = Author::find()
            ->where(['bio' => null])
            ->orderBy(['id' => SORT_ASC])
            ->limit($this->limit)
            ->all();

        $photos = [];
        $filled = 0;

        foreach ($authors as $index => $author) {
            $profile = $client->author($author->full_name);
            if ($profile === null) {
                continue;
            }

            $author->bio = $this->bio($profile);
            $author->birth_date = $profile['birth'];
            $author->death_date = $profile['death'];
            $author->save(false, ['bio', 'birth_date', 'death_date']);
            $photos[(int) $author->id] = $client->authorPhotoUrl($profile['key']);
            $filled++;

            if (($index + 1) % 50 === 0) {
                $this->stdout(sprintf('  профилей обработано %d…%s', $index + 1, PHP_EOL));
            }
        }

        $this->stdout(sprintf('Профилей заполнено: %d.%s', $filled, PHP_EOL));
        $this->stdout(sprintf('Фотографий сохранено: %d.%s', $this->downloadPhotos($photos), PHP_EOL));

        return ExitCode::OK;
    }

    /**
     * @param array{birth: ?string, death: ?string, topWork: ?string, works: int, subjects: list<string>} $profile
     */
    private function bio(array $profile): string
    {
        $parts = [];

        if ($profile['works'] > 0) {
            $parts[] = sprintf('Известен по %d произведениям в каталоге Open Library.', $profile['works']);
        }

        if ($profile['topWork'] !== null) {
            $parts[] = sprintf('Самая известная работа — «%s».', $profile['topWork']);
        }

        if ($profile['subjects'] !== []) {
            $parts[] = 'Основные темы: ' . implode(', ', $profile['subjects']) . '.';
        }

        return $parts === [] ? 'Сведения об авторе уточняются.' : implode(' ', $parts);
    }

    /**
     * @param array<int, string> $photos
     */
    private function downloadPhotos(array $photos): int
    {
        if ($photos === []) {
            return 0;
        }

        $http = \Yii::$container->get(ClientInterface::class);
        $storage = \Yii::$container->get(FileStorageInterface::class);
        $saved = 0;

        $requests = static function () use ($photos): \Generator {
            foreach ($photos as $authorId => $url) {
                yield $authorId => new Request('GET', $url . '?default=false');
            }
        };

        $pool = new Pool($http, $requests(), [
            'concurrency' => $this->concurrency,
            'options' => ['timeout' => 20, 'http_errors' => false],
            'fulfilled' => static function (ResponseInterface $response, int $authorId) use ($storage, &$saved): void {
                if ($response->getStatusCode() !== 200) {
                    return;
                }

                $body = (string) $response->getBody();
                if (strlen($body) < self::MIN_PHOTO_BYTES) {
                    return;
                }

                Author::updateAll(['photo_path' => $storage->saveContents($body, 'jpg')], ['id' => $authorId]);
                $saved++;
            },
            'rejected' => static fn(GuzzleException $reason, int $authorId): null => null,
        ]);

        $pool->promise()->wait();

        return $saved;
    }
}
