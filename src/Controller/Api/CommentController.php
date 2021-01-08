<?php

namespace App\Controller\Api;


use App\Entity\Comment;
use App\Entity\Post;
use App\Repository\CommentRepository;
use App\Traits\ApiTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CommentController
 * @package App\Controller
 * @Route("/api", name="comment_api")
 */
class CommentController extends AbstractController
{
    use ApiTrait;

    /**
     * @param CommentRepository $commentRepository
     * @return JsonResponse
     * @Route("/comments", name="comments", methods={"GET"})
     */
    public function getComments(CommentRepository $commentRepository){
        $data = $commentRepository->findAll();
        return $this->response($data);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param CommentRepository $commentRepository
     * @return JsonResponse
     * @Route("/comment", name="comment_add", methods={"POST"})
     */
    public function addComment(Request $request, EntityManagerInterface $entityManager, CommentRepository $commentRepository){

        try{
            $request = $this->transformJsonBody($request);

            if (!$request || !$request->get('post_id') || !$request->request->get('message')){
                throw new \Exception();
            }

            $comment = new Comment();
            $post = $this->getDoctrine()->getRepository(Post::class)->findOneBy(['id' => $request->get('post_id')]);

            $comment->setPost($post);
            $comment->setMessage($request->get('message'));
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->response($comment);

        }catch (\Exception $e){
            $data = [
                'status' => 422,
                'errors' => "Data no valid",
            ];
            return new JsonResponse($data, 422);
        }

    }

    /**
     * @param CommentRepository $commentRepository
     * @param $id
     * @return JsonResponse
     * @Route("/comment/{id}", name="comment_get", methods={"GET"})
     */
    public function getComment(CommentRepository $commentRepository, $id){
        $comment = $commentRepository->find($id);

        if (!$comment){
            $data = [
                'status' => 404,
                'errors' => "Comment not found",
            ];
            return new JsonResponse($data, 404);
        }
        return $this->response($comment);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param CommentRepository $commentRepository
     * @param $id
     * @return JsonResponse
     * @Route("/comment/{id}", name="comment_patch", methods={"PATCH"})
     */
    public function updateComment(Request $request, EntityManagerInterface $entityManager, CommentRepository $commentRepository, $id){

        try{
            $comment = $commentRepository->find($id);

            if (!$comment){
                $data = [
                    'status' => 404,
                    'errors' => "Comment not found",
                ];
                return new JsonResponse($data, 404);
            }

            $request = $this->transformJsonBody($request);

            if (!$request || !$request->get('post_id') || !$request->request->get('message')){
                throw new \Exception();
            }

            $post = $this->getDoctrine()->getRepository(Post::class)->findOneBy(['id' => $request->get('post_id')]);

            $comment->setPost($post);
            $comment->setMessage($request->get('message'));
            $entityManager->flush();

            return $this->response($comment);

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
     * @param CommentRepository $commentRepository
     * @param $id
     * @return JsonResponse
     * @Route("/comment/{id}", name="comment_delete", methods={"DELETE"})
     */
    public function deleteComment(EntityManagerInterface $entityManager, CommentRepository $commentRepository, $id){
        $comment = $commentRepository->find($id);

        if (!$comment){
            $data = [
                'status' => 404,
                'errors' => "Comment not found",
            ];
            return new JsonResponse($data, 404);
        }

        $entityManager->remove($comment);
        $entityManager->flush();

        $data = [
            'status' => 200,
            'errors' => "Comment deleted successfully",
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
                    'post' => [
                        'id' => $item->getPost()->getId(),
                        'message' => $item->getPost()->getMessage(),
                    ],
                    'message' => $item->getMessage()
                );
            }
        } else {
            $array = array(
                'id' => $data->getId(),
                'post' => [
                    'id' => $data->getPost()->getId(),
                    'message' => $data->getPost()->getMessage(),
                ],
                'message' => $data->getMessage()
            );
        }

        return new JsonResponse($array);
    }
}
