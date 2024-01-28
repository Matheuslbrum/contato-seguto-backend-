<?php

namespace Contatoseguro\TesteBackend\Controller;

use Contatoseguro\TesteBackend\Service\CompanyService;
use Contatoseguro\TesteBackend\Service\ProductService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ReportController
{
    private ProductService $productService;
    private CompanyService $companyService;

    public function __construct()
    {
        $this->productService = new ProductService();
        $this->companyService = new CompanyService();
    }

    public function generate(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $adminUserId = $request->getHeader('admin_user_id')[0];
        $action = $request->getQueryParams()['action'] ?? null;
        $productQs = $request->getQueryParams()['product'] ?? null;
        $action = str_replace('+', ' ', $action);
        $productQs = str_replace('+', ' ', $productQs);

        $data = [];
        $data[] = [
            'Id do produto',
            'Nome da Empresa',
            'Nome do Produto',
            'Valor do Produto',
            'Categorias do Produto',
            'Data de Criação',
            'Logs de Alterações'
        ];

        $stm = $this->productService->getAll($adminUserId);
        $products = $stm->fetchAll();

        foreach ($products as $i => $product) {
            $stm = $this->companyService->getNameById($product->company_id);
            $companyName = $stm->fetch()->name;

            $stm = $this->productService->getLog($product->id);
            $productLogs = $stm->fetchAll();

            $productLogsArray = array_map(function ($object) {
                $newValues = "$object->admin_name, $object->action, $object->timestamp";
                return $newValues;
            }, $productLogs);

            if ($action || $productQs) {
                if ($product->title == $productQs) {
                    $productLogsArray = array_filter($productLogsArray, function ($string) use ($action) {
                        return strpos($string, $action) !== false;
                    });

                    usort($productLogsArray, function ($a, $b) {
                        $dataA = strtotime(substr($a, strrpos($a, ',') + 2));
                        $dataB = strtotime(substr($b, strrpos($b, ',') + 2));

                        return $dataB - $dataA;
                    });

                    $productLogsArray = array_slice($productLogsArray, 0, 1);
                }
            }

            $productLogsValue = implode('<br>', $productLogsArray);

            $data[$i + 1][] = $product->id;
            $data[$i + 1][] = $companyName;
            $data[$i + 1][] = $product->title;
            $data[$i + 1][] = $product->price;
            $data[$i + 1][] = $product->category;
            $data[$i + 1][] = $product->created_at;
            $data[$i + 1][] = $productLogsValue;
        }

        $report = "<table style='font-size: 10px; border-collapse: collapse;'>";
        foreach ($data as $row) {
            $report .= "<tr>";
            foreach ($row as $column) {
                $report .= "<td style='border: 1px solid #ddd;'>{$column}</td>";
            }
            $report .= "</tr>";
        }
        $report .= "</table>";

        $response->getBody()->write($report);
        return $response->withStatus(200)->withHeader('Content-Type', 'text/html');
    }
}
