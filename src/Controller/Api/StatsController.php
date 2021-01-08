<?php

namespace App\Controller\Api;


use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CommentController
 * @package App\Controller
 * @Route("/api", name="stats_api")
 */
class StatsController extends AbstractController
{
    /**
     * @param CategoryRepository $categoryRepository
     * @param PostRepository $postRepository
     * @return JsonResponse
     * @Route("/stats", name="stats", methods={"GET"})
     */
    public function getStats(CategoryRepository $categoryRepository, PostRepository $postRepository){
        $post = $postRepository->getMostPopularPost();
        $allCategories = $categoryRepository->getMostPopularCategories();
        $categories = [];
        foreach ($allCategories as $category) {
            $categories[] = [
                'id' => $category[0]->getId(),
                'name' => $category[0]->getName(),
                'post_avg' => $category['pcount'],
            ];
        }

        $data = [
            "top_post_by_comment_count" => [
                "name" => "Most discussed post",
                "post_id" => $post[0][0]->getId(),
                "comment_count" => $post[0]['pc'],
            ],
            "average_comments_by_category" => [
                "name" => "Most active categories",
                "data" => $categories
            ]
        ];

        return new JsonResponse($data);
    }
}
