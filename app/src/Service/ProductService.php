<?php

namespace Contatoseguro\TesteBackend\Service;

use Contatoseguro\TesteBackend\Config\DB;
use Contatoseguro\TesteBackend\Service\CategoryService;

class ProductService
{
    private \PDO $pdo;
    private CategoryService $service;
    public function __construct()
    {
        $this->pdo = DB::connect();
        $this->service = new CategoryService();
    }

    public function getAll($adminUserId)
    {
        $query = "
            SELECT p.*, c.title as category
            FROM product p
            INNER JOIN product_category pc ON p.id = pc.product_id
            INNER JOIN category c ON c.id = pc.cat_id
            WHERE p.company_id = {$adminUserId}
        ";

        $stm = $this->pdo->prepare($query);

        $stm->execute();

        return $stm;
    }

    public function getOne($id)
    {
        $stm = $this->pdo->prepare("
            SELECT *
            FROM product
            WHERE id = {$id}
        ");
        $stm->execute();

        return $stm;
    }

    public function insertOne($body, $adminUserId)
    {
        $stm = $this->pdo->prepare("
            INSERT INTO product (
                company_id,
                title,
                price,
                active
            ) VALUES (
                {$body['company_id']},
                '{$body['title']}',
                {$body['price']},
                {$body['active']}
            )
        ");
        if (!$stm->execute())
            return false;

        $productId = $this->pdo->lastInsertId();

        $stm = $this->pdo->prepare("
            INSERT INTO product_category (
                product_id,
                cat_id
            ) VALUES (
                {$productId},
                {$body['category_id']}
            );
        ");
        if (!$stm->execute())
            return false;

        $stm = $this->pdo->prepare("
            INSERT INTO product_log (
                product_id,
                admin_user_id,
                `action`
            ) VALUES (
                {$productId},
                {$adminUserId},
                'create'
            )
        ");

        return $stm->execute();
    }

    public function updateOne($id, $body, $adminUserId)
    {
        $oldProduct = $this->getOne($id)->fetch();
        $oldCategory = $this->service->getProductCategory($id)->fetch();
        $oldProductArray = get_object_vars($oldProduct);
        $oldCategoryArray = get_object_vars($oldCategory);
        unset($oldProductArray['id']);
        unset($oldProductArray['created_at']);

        $oldProductArray['category_id'] = $oldCategoryArray['id'];

        $changes = [];

        if ($oldProductArray['title'] != $body['title']) {
            $changes['title'] = $body['title'];
        }

        if ($oldProductArray['price'] != $body['price']) {
            $changes['price'] = $body['price'];
        }

        if ($oldProductArray['active'] != $body['active']) {
            $changes['active'] = $body['active'];
        }
        if ($oldProductArray['category_id'] != $body['category_id']) {
            $changes['category_id'] = $body['category_id'];
        }


        $stm = $this->pdo->prepare("
            UPDATE product
            SET company_id = {$body['company_id']},
                title = '{$body['title']}',
                price = {$body['price']},
                active = {$body['active']}
            WHERE id = {$id}
        ");
        if (!$stm->execute())
            return false;

        $stm = $this->pdo->prepare("
            UPDATE product_category
            SET cat_id = {$body['category_id']}
            WHERE product_id = {$id}
        ");
        if (!$stm->execute())
            return false;

        foreach ($changes as $field => $value) {
            $stm = $this->pdo->prepare("
            INSERT INTO product_log (
                product_id,
                admin_user_id,
                `action`
            ) VALUES (
                {$id},
                {$adminUserId},
                'update {$field}'
            )
        ");
            $stm->execute();
        }

        return $stm;
    }

    public function deleteOne($id, $adminUserId)
    {
        $stm = $this->pdo->prepare("
            DELETE FROM product_category WHERE product_id = {$id}
        ");
        if (!$stm->execute())
            return false;

        $stm = $this->pdo->prepare("DELETE FROM product WHERE id = {$id}");
        if (!$stm->execute())
            return false;

        $stm = $this->pdo->prepare("
            INSERT INTO product_log (
                product_id,
                admin_user_id,
                `action`
            ) VALUES (
                {$id},
                {$adminUserId},
                'delete'
            )
        ");

        return $stm->execute();
    }

    public function getLog($id)
    {
        $stm = $this->pdo->prepare("
            SELECT pl.*, au.name as admin_name
            FROM product_log pl
            INNER JOIN admin_user au ON pl.admin_user_id = au.id
            WHERE product_id = {$id}
        ");
        $stm->execute();

        return $stm;
    }
}
