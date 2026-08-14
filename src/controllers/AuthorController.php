<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Author;
use app\models\forms\AuthorSearch;
use app\rbac\Permissions;
use app\services\catalog\CatalogService;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

final class AuthorController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly CatalogService $catalogue,
        array $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => ['create', 'update', 'delete'],
                        'allow' => true,
                        'roles' => [Permissions::MANAGE_CATALOG],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['POST']],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $search = new AuthorSearch();
        $search->load(\Yii::$app->request->queryParams);

        return $this->render('index', [
            'search' => $search,
            'dataProvider' => $this->catalogue->authors($search),
            'genres' => $this->catalogue->genres(),
        ]);
    }

    public function actionView(int $id): string
    {
        $author = $this->catalogue->findAuthor($id);

        return $this->render('view', [
            'author' => $author,
            'books' => $this->catalogue->booksOfAuthor($author),
        ]);
    }

    public function actionCreate(): Response|string
    {
        $author = new Author();

        if ($author->load(\Yii::$app->request->post()) && $this->catalogue->saveAuthor($author)) {
            return $this->redirect(['view', 'id' => $author->id]);
        }

        return $this->render('create', ['author' => $author]);
    }

    public function actionUpdate(int $id): Response|string
    {
        $author = $this->catalogue->findAuthor($id);

        if ($author->load(\Yii::$app->request->post()) && $this->catalogue->saveAuthor($author)) {
            return $this->redirect(['view', 'id' => $author->id]);
        }

        return $this->render('update', ['author' => $author]);
    }

    public function actionDelete(int $id): Response
    {
        $this->catalogue->deleteAuthor($this->catalogue->findAuthor($id));

        return $this->redirect(['index']);
    }
}
