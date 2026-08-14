<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\forms\BookForm;
use app\models\forms\BookSearch;
use app\rbac\Permissions;
use app\services\catalog\BookService;
use app\services\catalog\CatalogService;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

final class BookController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly BookService $books,
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
        $search = new BookSearch();
        $search->load(\Yii::$app->request->queryParams);

        return $this->render('index', [
            'search' => $search,
            'dataProvider' => $this->catalogue->books($search),
            'genres' => $this->catalogue->genres(),
        ]);
    }

    public function actionView(int $id): string
    {
        return $this->render('view', ['book' => $this->catalogue->findBook($id)]);
    }

    public function actionCreate(): Response|string
    {
        $form = new BookForm();

        if ($form->load(\Yii::$app->request->post()) && $form->validate()) {
            return $this->redirect(['view', 'id' => $this->books->create($form)->id]);
        }

        return $this->render('create', ['form' => $form, 'genres' => $this->catalogue->genres()]);
    }

    public function actionUpdate(int $id): Response|string
    {
        $book = $this->catalogue->findBook($id);
        $form = BookForm::fromBook($book);

        if ($form->load(\Yii::$app->request->post()) && $form->validate()) {
            $this->books->update($book, $form);

            return $this->redirect(['view', 'id' => $book->id]);
        }

        return $this->render('update', ['form' => $form, 'book' => $book, 'genres' => $this->catalogue->genres()]);
    }

    public function actionDelete(int $id): Response
    {
        $this->books->delete($this->catalogue->findBook($id));

        return $this->redirect(['index']);
    }
}
