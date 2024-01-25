<?php

namespace Contatoseguro\TesteBackend\Controller;

use Contatoseguro\TesteBackend\Model\Product;
use Contatoseguro\TesteBackend\Service\CategoryService;
use Contatoseguro\TesteBackend\Service\ProductService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProductController
{
    private ProductService $service;
    private CategoryService $categoryService;

    public function __construct()
    {
        $this->service = new ProductService();
        $this->categoryService = new CategoryService();
    }

    public function getAll(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $adminUserId = $request->getHeader('admin_user_id')[0];

        $stm = $this->service->getAll($adminUserId);
        $response->getBody()->write(json_encode($stm->fetchAll()));
        return $response->withStatus(200);
    }

    public function getAllByStatusFiltering(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $adminUserId = $request->getHeader('admin_user_id')[0];
        $status = $request->getQueryParams()['status'] ?? null;

        $stm = $this->service->getAllByStatusFiltering($adminUserId, $status);
        $response->getBody()->write(json_encode($stm->fetchAll()));
        return $response->withStatus(200);
    }

    public function getAllByCategoryFiltering(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $adminUserId = $request->getHeader('admin_user_id')[0];
        $category = $request->getQueryParams()['category'] ?? null;

        $stm = $this->service->getAllByCategoryFiltering($adminUserId, $category);
        $response->getBody()->write(json_encode($stm->fetchAll()));
        return $response->withStatus(200);
    }

    public function getAllByOrder(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $adminUserId = $request->getHeader('admin_user_id')[0];
        $order = $request->getQueryParams()['order'] ?? 'asc';
        $order = in_array(strtolower($order), ['asc', 'desc']) ? strtolower($order) : 'asc';

        $stm = $this->service->getAllByOrder($adminUserId, $order)->fetchAll();

        $newStm = array($stm);


        $response->getBody()->write(json_encode($stm));
        return $response->withStatus(200);
    }

    public function getOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $stm = $this->service->getOne($args['id']);
        $product = Product::hydrateByFetch($stm->fetch());

        $adminUserId = $request->getHeader('admin_user_id')[0];
        $productCategory = $this->categoryService->getProductCategory($product->id)->fetchAll();
        foreach ($productCategory as $category) {
            $fetchedCategory = $this->categoryService->getOne($adminUserId, $category->id)->fetch();
            $fetchedCategorys = array($fetchedCategory);
            foreach ($fetchedCategorys as $categorys) {
                $product->setCategory($categorys->title);
                $response->getBody()->write(json_encode($product));
            }
        }

        return $response->withStatus(200);
    }

    public function insertOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = $request->getParsedBody();
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if ($this->service->insertOne($body, $adminUserId)) {
            return $response->withStatus(200);
        } else {
            return $response->withStatus(404);
        }
    }

    public function updateOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = $request->getParsedBody();
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if ($this->service->updateOne($args['id'], $body, $adminUserId)) {
            return $response->withStatus(200);
        } else {
            return $response->withStatus(404);
        }
    }

    public function deleteOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if ($this->service->deleteOne($args['id'], $adminUserId)) {
            return $response->withStatus(200);
        } else {
            return $response->withStatus(404);
        }
    }
}
