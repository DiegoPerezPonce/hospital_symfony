<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

final class NurseController extends AbstractController
{
    // Nosotros definimos la ruta al archivo JSON donde guardamos los datos de los enfermeros.
    private const NURSES_FILE = __DIR__ . '/../../public/nurses.json';

    // Esta es la ruta principal de nuestra API.
    #[Route('/nurse/index', name: 'app_nurse_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        // Nosotros devolvemos un mensaje de bienvenida.
        return $this->json([
            'message' => 'Bienvenido a nuestra API de enfermeros!',
            'path' => 'src/Controller/NurseController.php',
        ]);
    }

    // Esta ruta se encarga del proceso de inicio de sesión para los enfermeros.
    #[Route('/nurse/login', name: 'app_nurse_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        // Nosotros decodificamos el contenido JSON que recibimos en la solicitud.
        $data = json_decode($request->getContent(), true);

        // Validamos si el contenido JSON que nos enviaron es válido.
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(
                ['success' => false, 'message' => 'Contenido JSON inválido.'],
                Response::HTTP_BAD_REQUEST // 400 Bad Request
            );
        }

        // Nosotros obtenemos el 'user' y 'pw' de los datos, o 'null' si no están presentes.
        $user = $data['user'] ?? null;
        $pw = $data['pw'] ?? null;

        // Verificamos que tanto el 'user' como el 'pw' hayan sido proporcionados.
        if (!$user || !$pw) {
            return $this->json(
                ['success' => false, 'message' => 'Faltan user o pw en la solicitud.'],
                Response::HTTP_BAD_REQUEST // 400 Bad Request
            );
        }

        $filesystem = new Filesystem();
        // Nosotros comprobamos si el archivo de enfermeros existe en la ubicación especificada.
        if (!$filesystem->exists(self::NURSES_FILE)) {
            // En una aplicación real, registraríamos este error.
            error_log('Error: nurses.json file not found at ' . self::NURSES_FILE);
            return $this->json(
                ['success' => false, 'message' => 'Error interno del servidor: archivo de enfermeros no encontrado.'],
                Response::HTTP_INTERNAL_SERVER_ERROR // 500 Internal Server Error
            );
        }

        // Intentamos leer todo el contenido del archivo de enfermeros.
        $nursesContent = file_get_contents(self::NURSES_FILE);

        if ($nursesContent === false) {
             // También registraríamos este error.
            error_log('Error: Could not read nurses.json file.');
            return $this->json(
                ['success' => false, 'message' => 'Error interno del servidor: no se pudo leer el archivo de enfermeros.'],
                Response::HTTP_INTERNAL_SERVER_ERROR // 500 Internal Server Error
            );
        }

        // Decodificamos el contenido del archivo JSON para trabajarlo como un array asociativo.
        $nurses = json_decode($nursesContent, true);

        // Verificamos si hubo algún error al decodificar el archivo JSON.
        if ($nurses === null && json_last_error() !== JSON_ERROR_NONE) {
            // Registramos el detalle del error de decodificación.
            error_log('Error: Failed to decode nurses.json: ' . json_last_error_msg());
            return $this->json(
                ['success' => false, 'message' => 'Error interno del servidor: error al decodificar el archivo de enfermeros.'],
                Response::HTTP_INTERNAL_SERVER_ERROR // 500 Internal Server Error
            );
        }

        // Si el resultado no es un array (por ejemplo, el archivo estaba vacío o mal formado), nosotros lo inicializamos como un array vacío.
        if (!is_array($nurses)) {
             $nurses = [];
        }

        // Nosotros iteramos a través de cada enfermero en nuestro listado.
        foreach ($nurses as $nurse) {
            // Comprobamos si las credenciales ('user' y 'pw') del enfermero actual coinciden con las proporcionadas.
            if (isset($nurse['user']) && isset($nurse['pw']) &&
                $nurse['user'] === $user && $nurse['pw'] === $pw) {
                // Si encontramos una coincidencia, el inicio de sesión es exitoso.
                return $this->json(
                    ['success' => true, 'message' => 'Login exitoso.'],
                    Response::HTTP_OK // 200 OK
                );
            }
        }

        // Si nosotros llegamos a este punto, significa que no encontramos ningún enfermero con las credenciales dadas.
        return $this->json(
            ['success' => false, 'message' => 'Credenciales inválidas.'],
            Response::HTTP_UNAUTHORIZED // 401 Unauthorized para credenciales incorrectas
        );
    }
}
