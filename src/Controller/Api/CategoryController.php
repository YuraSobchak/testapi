<?php

namespace App\Controller\Api;


use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Traits\ApiTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CategoryController
 * @package App\Controller
 * @Route("/api", name="category_api")
 */
class CategoryController extends AbstractController
{
    use ApiTrait;

    /**
     * @param CategoryRepository $categoryRepository
     * @return JsonResponse
     * @Route("/categories", name="categories", methods={"GET"})
     */
    public function getCategories(CategoryRepository $categoryRepository){
        $data = $categoryRepository->findAll();
        return $this->response($data);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param CategoryRepository $categoryRepository
     * @return JsonResponse
     * @Route("/category", name="category_add", methods={"POST"})
     */
    public function addCategory(Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository){

        try{
            $request = $this->transformJsonBody($request);

            if (!$request || !$request->get('name')){
                throw new \Exception();
            }

            $category = new Category();
            $category->setName($request->get('name'));
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->response($category);

        }catch (\Exception $e){
            $data = [
                'status' => 422,
                'errors' => "Data no valid",
            ];
            return new JsonResponse($data, 422);
        }

    }

    /**
     * @param CategoryRepository $categoryRepository
     * @param $id
     * @return JsonResponse
     * @Route("/category/{id}", name="category_get", methods={"GET"})
     */
    public function getCategory(CategoryRepository $categoryRepository, $id){
        $category = $categoryRepository->find($id);

        if (!$category){
            $data = [
                'status' => 404,
                'errors' => "Category not found",
            ];
            return new JsonResponse($data, 404);
        }
        return $this->response($category);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param CategoryRepository $categoryRepository
     * @param $id
     * @return JsonResponse
     * @Route("/category/{id}", name="category_patch", methods={"PATCH"})
     */
    public function updateCategory(Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository, $id){

        try{
            $category = $categoryRepository->find($id);

            if (!$category){
                $data = [
                    'status' => 404,
                    'errors' => "Category not found",
                ];
                return new JsonResponse($data, 404);
            }

            $request = $this->transformJsonBody($request);

            if (!$request || !$request->get('name')){
                throw new \Exception();
            }

            $category->setName($request->get('name'));
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->response($category);

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
     * @param CategoryRepository $categoryRepository
     * @param $id
     * @return JsonResponse
     * @Route("/category/{id}", name="category_delete", methods={"DELETE"})
     */
    public function deleteCategory(EntityManagerInterface $entityManager, CategoryRepository $categoryRepository, $id){
        $category = $categoryRepository->find($id);

        if (!$category){
            $data = [
                'status' => 404,
                'errors' => "Category not found",
            ];
            return new JsonResponse($data, 404);
        }

        $entityManager->remove($category);
        $entityManager->flush();

        $data = [
            'status' => 200,
            'errors' => " Category deleted successfully",
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
                    'name' => $item->getName()
                );
            }
        } else {
            $array = array(
                'id' => $data->getId(),
                'name' => $data->getName()
            );
        }

        return new JsonResponse($array);
    }
}
