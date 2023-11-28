<?php

  require 'config/config.php';
  require 'config/database.php';
  $db = new Database();
  $con = $db->conectar();

  $productos = isset($_SESSION['carrito']['productos']) ? $_SESSION['carrito']['productos'] : null;

  $lista_carrito = array();

  if($productos != null){
    foreach($productos as $clave => $cantidad){

        $sql = $con->prepare("SELECT id, nombre, precio, descuento, $cantidad AS cantidad FROM productos WHERE id=? AND activo=1");
        $sql->execute([$clave]);
        $lista_carrito[] = $sql->fetch(PDO::FETCH_ASSOC);

    } 
    
}else {
    header("location: index.php");
    exit; 
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Camisetas 360 Sports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link href="css/estilos.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/e403b51d68.js" crossorigin="anonymous"></script>
  </head>
<body>

<?php include 'menu.php'; ?>

<main>
  <div class="container">

    <div class="row">
        <div class="col-6">
            <h4>Detalles de pago</h4>
            <div class="mt-4" id="paypal-button-container"></div>
        </div>

        <div class="col-6">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                    <tbody>
                        <?php if($lista_carrito == null){
                            echo '<tr><td colspan="5" class="text-center"><b>Lista vacia</b></td></tr>';
                        }else {

                            $total = 0;
                            foreach($lista_carrito as $producto){
                            $_id = $producto['id'];
                            $nombre = $producto['nombre'];
                            $precio = $producto['precio'];
                            $descuento = $producto['descuento'];
                            $cantidad = $producto['cantidad'];
                            $precio_desc = $precio - (($precio * $descuento) / 100);
                            $subotal = $cantidad * $precio_desc;
                            $total += $subotal; 

                        ?>
                        <tr>
                            <td><?php echo $nombre; ?></td>
                            <td>
                                <div id="subtotal_<?php echo $_id; ?>" name="subtotal[]"><?php echo MONEDA . number_format($subotal,2, '.', ','); ?></div>
                            </td>
                        </tr>
                        <?php } ?>
                <tr>
                    <td colspan="2">
                        <p class="h3 text-end" id="total"><?php echo MONEDA . number_format($total,2, '.', ',');?></p>
                    </td>
                </tr>
                
                </tbody>
                <?php } ?>
            </table>
  </div>
        </div>
  </div>
  </div>
</main>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

<script src="https://www.paypal.com/sdk/js?client-id=<?php echo CLIENT_ID; ?>&currency=<?php echo CURRENCY; ?>"></script>



<script>
    paypal.Buttons({
        createOrder: (data, actions) => {
            return actions.order.create({
                purchase_units:[{
                    amount: {
                        value: '<?= $total; ?>'
                    }
                }]
            });
        },
        onApprove: (data,actions) => {
            let URL = 'clases/captura.php'
            actions.order.capture().then(function(detalles){

            console.log(detalles)

            let url = 'clases/captura.php'

            return fetch(url,{

                method: 'post',
                headers: {
                    'content-type': 'application/json'
                },
                body: JSON.stringify({
                    detalles:detalles
                })
            })
            });
        },
        onCancel: function(data) {
            alert("pago cancelado");
            console.log(data);
        }
    }).render('#paypal-button-container');

</script>

</body>
</html>