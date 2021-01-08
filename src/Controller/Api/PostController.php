<?php

namespace App\Controller\Api;


use App\Entity\Category;
use App\Entity\Post;
use App\Kernel;
use App\Repository\PostRepository;
use App\Traits\ApiTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PostController
 * @package App\Controller
 * @Route("/api", name="post_api")
 */
class PostController extends AbstractController
{
    use ApiTrait;

    /**
     * @param PostRepository $postRepository
     * @return JsonResponse
     * @Route("/posts", name="posts", methods={"GET"})
     */
    public function getPosts(PostRepository $postRepository){
        $data = $postRepository->findAll();
        return $this->response($data);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param PostRepository $postRepository
     * @return JsonResponse
     * @throws \Exception
     * @Route("/post", name="post_add", methods={"POST"})
     */
    public function addPost(Request $request, EntityManagerInterface $entityManager, PostRepository $postRepository){

        try{
            $request = $this->transformJsonBody($request);

            if (!$request || !$request->get('category_id') || !$request->request->get('message')){
                throw new \Exception();
            }

            $post = new Post();
            $category = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['id' => $request->get('category_id')]);

            $post->setCategory($category);
            $post->setMessage($request->get('message'));
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->response($post);

        }catch (\Exception $e){
            $data = [
                'status' => 422,
                'errors' => "Data no valid",
            ];
            return new JsonResponse($data, 422);
        }

    }

    /**
     * @param PostRepository $postRepository
     * @param $id
     * @return JsonResponse
     * @Route("/post/{id}", name="post_get", methods={"GET"})
     */
    public function getPost(PostRepository $postRepository, $id){
        $post = $postRepository->find($id);

        if (!$post){
            $data = [
                'status' => 404,
                'errors' => "Post not found",
            ];
            return new JsonResponse($data, 404);
        }
        return $this->response($post);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param PostRepository $postRepository
     * @param $id
     * @return JsonResponse
     * @Route("/post/{id}", name="post_patch", methods={"PATCH"})
     */
    public function updatePost(Request $request, EntityManagerInterface $entityManager, PostRepository $postRepository, $id){

        try{
            $post = $postRepository->find($id);

            if (!$post){
                $data = [
                    'status' => 404,
                    'errors' => "Post not found",
                ];
                return new JsonResponse($data, 404);
            }

            $request = $this->transformJsonBody($request);

            if (!$request || !$request->get('category_id') || !$request->request->get('message')){
                throw new \Exception();
            }

            $category = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['id' => $request->get('category_id')]);

            $post->setCategory($category);
            $post->setMessage($request->get('message'));
            $entityManager->flush();

            return $this->response($post);

        }catch (\Exception $e){
            $data = [
                'status' => 422,
                'errors' => "Data no valid",
            ];
            return new JsonResponse($data, 422);
        }

    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param PostRepository $postRepository
     * @param $id
     * @return JsonResponse
     * @Route("/post/{id}", name="post_delete", methods={"DELETE"})
     */
    public function deletePost(EntityManagerInterface $entityManager, PostRepository $postRepository, $id){
        $post = $postRepository->find($id);

        if (!$post){
            $data = [
                'status' => 404,
                'errors' => "Post not found",
            ];
            return new JsonResponse($data, 404);
        }

        $entityManager->remove($post);
        $entityManager->flush();

        $data = [
            'status' => 200,
            'errors' => "Post deleted successfully",
        ];
        return new JsonResponse($data);
    }

    public function response($data)
    {
        $array = array();

        if (is_array($data)) {
            foreach ($data as $item) {
                $array[] = array(
                    'id' => $item->getId(),
                    'category' => [
                        'id' => $item->getCategory()->getId(),
                        'name' => $item->getCategory()->getName(),
                    ],
                    'message' => $item->getMessage()
                );
            }
        } else {
            $array = array(
                'id' => $data->getId(),
                'category' => [
                    'id' => $data->getCategory()->getId(),
                    'name' => $data->getCategory()->getName(),
                ],
                'message' => $data->getMessage()
            );
        }

        return new JsonResponse($array);
    }
}
