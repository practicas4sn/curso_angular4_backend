<?php

require_once 'vendor/autoload.php';

$app = new \Slim\Slim();

$db = new mysqli('localhost', 'root', '', 'curso_angular4');

// Consfiguracion de cabeceras HHTP
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
    die();
}


$app->get("/pruebas", function () use ($app) {
    echo "Hola mundo desde SlimPHP";
});

$app->get("/probando", function () use ($app) {
    echo "Otro texto cualquiera";
});
/* Asignaremos a cada url un GET y un POST y le diremos que tiene que hacer al recibir la peticion*/

// LISTAR PRODUCTOS
$app->get('/productos', function () use ($db, $app) {
    $sql = "SELECT * FROM productos ORDER BY id DESC;";
    $query = $db->query($sql);

    $productos = array();
    while ($producto = $query->fetch_assoc()) {
        $productos[] = $producto;
    }

    $result = array(
        'status' => 'success',
        'code' => 200,
        'data' => $productos
    );

    echo json_encode($result);

});

// DEVOLVER UN SOLO PRODUCTO
$app->get('/producto/:id', function ($id) use ($db, $app) {
    $sql = "SELECT * FROM productos WHERE id = " . $id;
    $query = $db->query($sql);

    $result = array(
        'status' => 'error',
        'code' => 404,
        'message' => 'Producto no disponible'
    );

    if ($query->num_rows == 1) {
        $producto = $query->fetch_assoc();

        $result = array(
            'status' => 'success',
            'code' => 200,
            'data' => $producto
        );
    }

    echo json_encode($result);
});

// ELIMINAR UN PRODUCTO
$app->get("/delete-producto/:id", function ($id) use ($db, $app) {
    $sql = "DELETE FROM productos WHERE id = " . $id;
    $query = $db->query($sql);

    if ($query) {
        $result = array(
            'status' => 'success',
            'code' => 200,
            'message' => "Producto eliminado correctamente"
        );
    } else {
        $result = array(
            'status' => 'error',
            'code' => 404,
            'message' => 'Producto no eliminado'
        );
    }

    echo json_encode($result);

});

// ACTUALIZAR UN PRODUCTO
$app->post('/update-producto/:id', function ($id) use ($db, $app) {
    $json = $app->request->post('json');
    $data = json_decode($json, true);

    $sql = "UPDATE productos SET " .
        "nombre = '{$data["nombre"]}'," .
        "descripcion = '{$data["descripcion"]}',";

    if (isset($data['imagen'])) {
        $sql .= "imagen = '{$data["imagen"]}',";
    }
    $sql .= "precio = '{$data["precio"]}' WHERE id = {$id}";

    $query = $db->query($sql);

    if ($query) {
        $result = array(
            'status' => 'success',
            'code' => 200,
            'message' => "Producto actualizado correctamente"
        );
    } else {
        $result = array(
            'status' => 'error',
            'code' => 404,
            'message' => 'Producto no actualizado'
        );
    }

    echo json_encode($result);
});

// SUBIR IMAGEN A UN PRODUCTO
$app->post('/upload-file', function () use ($db, $app) {

    $result = array(
        'status' => 'error',
        'code' => 404,
        'message' => 'Imagen no subida'
    );

    if (isset($_FILES['uploads'])) {
        $piramideUploader = new PiramideUploader();
        $upload = $piramideUploader->upload('image', "uploads", "uploads", array("image/jpeg", "image/png", "image/png"));
        $file = $piramideUploader->getInfoFile();
        $file_name = $file['complete_name'];

        if (isset($upload) && $upload['uploaded'] == false) {
            $result = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Producto no actualizado'
            );
        } else {
            $result = array(
                'status' => 'success',
                'code' => 200,
                'message' => "Producto actualizado correctamente",
                'filename' => $file_name
            );
        }
    }

    echo json_encode($result);

});

// GUARDAR PRODUCTOS
$app->post('/productos', function () use ($app, $db) {
    $json = $app->request->post('json');
    $data = json_decode($json, true);

    if (!isset($data['nombre'])) {
        $data['nombre'] = null;
    }

    if (!isset($data['descripcion'])) {
        $data['descripcion'] = null;
    }

    if (!isset($data['precio'])) {
        $data['precio'] = null;
    }

    if (!isset($data['imagen'])) {
        $data['imagen'] = null;
    }

    $query = "INSERT INTO productos VALUES(NULL," .
        "'{$data['nombre']}'," .
        "'{$data['descripcion']}'," .
        "'{$data['precio']}'," .
        "'{$data['imagen']}'" .
        ");";

    var_dump($query);
    $insert = $db->query($query);

    $result = array(
        'status' => 'error',
        'code' => 404,
        'message' => 'Producto NO se ha creado'
    );

    if ($insert) {
        $result = array(
            'status' => 'success',
            'code' => 200,
            'message' => 'Producto creado correctamente'
        );
    }

    echo json_encode($result);
});

$app->run();