<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class NurseController extends AbstractController
{
    #[Route('/nurse/index', name: 'get_all_nurses', methods: ['GET'])]
    public function getAllNurses(): JsonResponse
    {
        $nurses = [
            ['id' => 1, 'name' => 'Maria Lopez', 'specialty' => 'Pediatría', 'email' => 'maria.lopez@example.com'],
            ['id' => 2, 'name' => 'Juan Perez', 'specialty' => 'Urgencias', 'email' => 'juan.perez@example.com'],
            ['id' => 3, 'name' => 'Ana Garcia', 'specialty' => 'Oncología', 'email' => 'ana.garcia@example.com'],
        ];

        return new JsonResponse($nurses);
    }

    #[Route('/nurse/login', name: 'nurse_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = $data['user'] ?? null;
        $password = $data['pw'] ?? null;

        $validUsers = [
            'maria.lopez' => 'pass123',
            'juan.perez' => 'pass456',
        ];

        if ($user && $password && isset($validUsers[$user]) && $validUsers[$user] === $password) {
            return new JsonResponse(['message' => 'Login successful', 'status' => true]);
        }

        return new JsonResponse(['message' => 'Invalid credentials', 'status' => false], JsonResponse::HTTP_UNAUTHORIZED);
    }

    #[Route('/nurse/name/{name}', name: 'find_nurse_by_name', methods: ['GET'])]
    public function findNurseByName(string $name): JsonResponse
    {
        $nurses = [
            ['id' => 1, 'name' => 'Maria Lopez', 'specialty' => 'Pediatría', 'email' => 'maria.lopez@example.com'],
            ['id' => 2, 'name' => 'Juan Perez', 'specialty' => 'Urgencias', 'email' => 'juan.perez@example.com'],
            ['id' => 3, 'name' => 'Ana Garcia', 'specialty' => 'Oncología', 'email' => 'ana.garcia@example.com'],
            ['id' => 4, 'name' => 'Maria Rodriguez', 'specialty' => 'Cardiología', 'email' => 'maria.rodriguez@example.com'],
        ];

        $foundNurses = array_filter($nurses, fn($nurse) => stripos($nurse['name'], $name) !== false);

        return !empty($foundNurses)
            ? new JsonResponse(array_values($foundNurses))
            : new JsonResponse(['message' => 'Nurse not found'], JsonResponse::HTTP_NOT_FOUND);
    }
}

