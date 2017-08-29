<?php

namespace Gentor\Olx\Api;


/**
 * Class Categories
 * @package Gentor\Olx\Api
 */
class Categories
{
    /** @var Client $client */
    private $client;

    /**
     * Categories constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param null $parentId
     * @return array|\stdClass
     */
    public function get($parentId = null)
    {
        $response = $this->client->request('GET', 'open/categories' . ($parentId ? ('/' . $parentId) : ''));

        if ($parentId) {
            return $response;
        }

        return !empty($response->results) ? $response->results : [];
    }

    /**
     * @param null $parentId
     * @return array|\stdClass
     */
    public function getRecursive($parentId = null)
    {
        $categories = $this->get($parentId);

        if (is_array($categories)) {
            foreach ($categories as $category) {
                if (!empty($category->children)) {
                    foreach ($category->children as $row => $child) {
                        $category->children[$row] = $this->getRecursive($child);
                    }
                }
            }
        } elseif (is_object($categories) && !empty($categories->children)) {
            foreach ($categories->children as $row => $child) {
                $categories->children[$row] = $this->getRecursive($child);
            }
        }

        return $categories;
    }

    /**
     * @param null $categories
     * @return array
     */
    public function getFlat($categories = null)
    {
        $categories = is_null($categories) ? $this->getRecursive() : $categories;
        $result = [];

        foreach ($categories as $category) {
            $result[] = (object)[
                'id' => $category->id,
                'name' => array_values((array)$category->names)[0],
                'parent_id' => !empty($category->parent_id) ? $category->parent_id : null,
            ];

            if (!empty($category->children)) {
                $result = array_merge($result, $this->getFlat($category->children));
            }
        }

        return $result;
    }
}