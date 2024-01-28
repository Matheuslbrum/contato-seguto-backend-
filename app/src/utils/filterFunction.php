<?php

function getFilteredProducts($stm, $order, $status, $category)
{
  if ($order || $status || $category) {
    $newStm = $stm;

    if ($order) {
      usort($newStm, function ($productA, $productB) use ($order) {
        $dateA = strtotime($productA->created_at);
        $dateB = strtotime($productB->created_at);

        if ($order === 'asc') {
          return $dateA <=> $dateB;
        } else {
          return $dateB <=> $dateA;
        }
      });
    }

    if ($category) {
      $newStm = array_filter($newStm, function ($product) use ($category) {
        return $product->category == $category;
      });
    }

    if ($status) {
      $newStm = array_filter($newStm, function ($product) use ($status) {
        return $product->active == ($status === "active" ? 1 : 0);
      });
      $newStm = array_values($newStm);
    }

    return $newStm;
  } else {
    return $stm;
  }
}
