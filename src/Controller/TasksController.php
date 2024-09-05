<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Tasks;

#[Route('/api', name: 'api_')]
class TasksController extends AbstractController
{
    //get
    #[Route('/tasks', name: 'app_index', methods: ['get'])]
    public function index(ManagerRegistry $doctrine): JsonResponse
    {
        $tasks = $doctrine
            ->getRepository(Tasks::class)
            ->findAll();

        $data = [];

        foreach ($tasks as $task) {
            $data[] = [
                'id' => $task->getId(),
                'name' => $task->getName(),
                'description' => $task->getDescription(),
                'date' => $task->getDate(),
            ];
        }

        return $this->json($data);
    }

    //post 
    #[Route('/tasks', name: 'task_create', methods: ['post'])]
    public function create(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        // Obtém o conteúdo do corpo da requisição e decodifica o JSON
        $data = json_decode($request->getContent(), true);

        // Verifica se a decodificação foi bem-sucedida e se os campos esperados existem
        if (!$data || !isset($data['name'], $data['description'], $data['date'])) {
            return $this->json('Dados inválidos fornecidos', 400);
        }

        $name = $data['name'];
        $description = $data['description'];
        $date = $data['date'];

        // Verifica se os parâmetros foram fornecidos
        if (empty($name) || empty($description) || empty($date)) {
            return $this->json(['error' => 'Dados inválidos'], 400);
        }


        $task = new Tasks();
        $task->setName($name);
        $task->setDescription($description);
        $task->setDate($date);

        $entityManager->persist($task);
        $entityManager->flush();

        // Dados para o retorno
        $responseData = [
            'id' => $task->getId(),
            'name' => $task->getName(),
            'description' => $task->getDescription(),
            'date' => $task->getDate(), 
        ];

        return $this->json($responseData);
    }


    #[Route('/tasks/{id}', name: 'task_update', methods: ['put', 'patch'])]
    public function update(ManagerRegistry $doctrine, Request $request, int $id): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $task = $entityManager->getRepository(Tasks::class)->find($id);

        if (!$task) {
            return $this->json('Não achei a task com o id: ' . $id, 404);
        }

        // Decodifica o conteúdo JSON do corpo da requisição
        $data = json_decode($request->getContent(), true);

        // Verifica se a decodificação foi bem-sucedida e se os campos esperados existem
        if (!$data || !isset($data['name'], $data['description'], $data['date'])) {
            return $this->json('Dados inválidos fornecidos', 400);
        }

        // Atualiza os campos da tarefa com os dados recebidos
        $task->setName($data['name']);
        $task->setDescription($data['description']);
        $task->setDate($data['date']); // Se o método setDate espera uma string

        $entityManager->flush();

        $responseData = [
            'id' => $task->getId(),
            'name' => $task->getName(),
            'description' => $task->getDescription(),
            'date' => $task->getDate(), // Ajuste conforme o formato retornado
        ];

        return $this->json($responseData);
    }

    #[Route('/tasks/{id}', name: 'task_show', methods: ['get'])]
    public function show(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        $task = $doctrine->getRepository(Tasks::class)->find($id);

        if (!$task) {
            return $this->json('Nao achei ' . $id, 404);
        }

        $data =  [
            'id' => $task->getId(),
            'name' => $task->getName(),
            'description' => $task->getDescription(),
            'date' => $task->getDate(),
        ];

        return $this->json($data);
    }




    #[Route('/tasks/{id}', name: 'task_delete', methods: ['delete'])]
    public function delete(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $task = $entityManager->getRepository(Tasks::class)->find($id);

        if (!$task) {
            return $this->json('Não achei a tarefa com id:  ' . $id, 404);
        }

        $entityManager->remove($task);
        $entityManager->flush();

        return $this->json('Deletado com sucesso, a Tarefa com id :  ' . $id);
    }
}
