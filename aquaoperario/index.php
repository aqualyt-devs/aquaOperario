<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0" />
    <title>Aqualyt - Operario</title>
    <?php include 'styles.php';?>
</head>

<body>
    <?php

    include 'urlAPI.php';
    include 'checkToken.php';

ini_set("allow_url_fopen", 1);

function get_content($URL){
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_URL, $URL);
      $data = curl_exec($ch);
      curl_close($ch);
      return $data;
}

const AQUATOKEN = "AquaToken";
const AQUATOKENNOMBRE = "AquaTokenNOMBRE";
const AQUATOKENID = "AquaTokenID";

$errorAcceso = false;

if(isset($_POST['formSent'])){
    $formSent = $_POST['formSent'];
} else {
    $formSent = false;
}

if((isset($_GET['logout'])) && ($_GET['logout'])) {
    setcookie(AQUATOKEN, "0", time() - 1,'/');
    setcookie(AQUATOKENNOMBRE, "0", time() - 1,'/');
    setcookie(AQUATOKENID, "0", time() - 1,'/');
    header("Refresh:0; url=index.php");
}

if ($formSent) {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    $url = $urlAPI . '/oficina/auth?acc=' . $usuario . '&pw=' . $password;

    $json = get_content($url);
    $obj = json_decode($json);

    if ($obj->codigo == 100) {
        // la duración de las cookies es de 12 horas (43200 segundos)
        setcookie(AQUATOKEN, $obj->contenido->TOKEN, time() + 43200,'/');
        setcookie(AQUATOKENNOMBRE, $obj->contenido->EMPLEADO_NOMBRE, time() + 43200,'/');
        setcookie(AQUATOKENID, $obj->contenido->EMPLEADO_ID, time() + 43200,'/');
        header("Refresh:0");
    } else {
        $errorAcceso = true;
    }
}

if ((!isset($_COOKIE[AQUATOKEN])) || (checkToken($urlAPI)->codigo==200)) {?>
        <div class="navbar-fixed">
        <nav class="orange darken-3" role="navigation">
            <div class="nav-wrapper">
                <a id="logo-container" href="index.php" class="brand-logo">
                    <img src="imgs/logo_negativo.png">
                </a>
            </div>
        </nav>
    </div>
    <main>
        <div class="section">
            <div class="row">
                <form action="index.php" class="col offset-s3 s6" method="post">
                    <div class="row center-align">
                        <img src="imgs/logo.jpg" alt="">
                    </div>
                    <?php if ($errorAcceso) {?>
                    <div class="row">
                        <div class="col s12">
                            <div class="card-panel red lighten-4">
                                <span class="red-text darken-4">Error de acceso, comprueba el nombre de usuario y contraseña.</span>
                            </div>
                        </div>
                    </div>
                    <?php }?>
                    <div class="row">
                        <div class="input-field col s12">
                        <input id="usuario" name="usuario" type="text" class="validate">
                        <label for="usuario">Usuario</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                        <input id="password" name="password" type="password" class="validate">
                        <label for="password">Password</label>
                        </div>
                    </div>
                    <div class="row">
                    <input type="hidden" name="formSent" value="true">
                    <button class="btn waves-effect waves-light" type="submit" name="action">Entrar
                        <i class="material-icons right">send</i>
                    </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script src="js/materialize.min.js"></script>
<?php } else {
    ?>
    <div class="navbar-fixed">
        <nav class="orange  darken-3" role="navigation">
            <div class="nav-wrapper">
                <a id="logo-container" href="index.php" class="brand-logo">
                    <img src="imgs/logo_negativo.png">
                </a>
                <a href="#" data-activates="mobileMenu" class="button-collapse"><i class="material-icons">menu</i></a>
                <ul class="right hide-on-med-and-down">
                    <li>
                        <a href="../aquaparrilla">
                            <i class="material-icons left">account_circle</i>AquaParrilla</a>
                    </li>
                    <li>
                        <a href="index.php?logout=1">
                            <i class="material-icons left">exit_to_app</i><?php echo $_COOKIE[AQUATOKENNOMBRE]; ?></a>
                    </li>
                </ul>
                <ul class="side-nav" id="mobileMenu">
                    <li>
                        <a href="../aquaparrilla">
                            <i class="material-icons left">account_circle</i>AquaParrilla</a>
                    </li>
                    <li>
                        <a href="index.php?logout=1">
                            <i class="material-icons left">exit_to_app</i><?php echo $_COOKIE[AQUATOKENNOMBRE]; ?></a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>

    <main>
        <div class="section">
            <div class="row">
                <div class="col s5" id="equiposListParrilla">
                    <div class="progress" id="loadingEquipos">
                        <div class="indeterminate"></div>
                    </div>
                    <ul class="collection z-depth-2 optiscroll columnHeight" id="equiposListCol">
                    </ul>
                </div>
                <div class="col s7" id="buttonsAvisos">
                    <div class="progress" id="loadingButtons">
                        <div class="indeterminate"></div>
                    </div>
                    <div class="row">
                        <div class="col s12">
                            <h4>
                                <span id="ModalPSTitle"></span>
                                <span id="PSLink">cargando...</span>
                            </h4>
                        </div>
                    </div>
                    <div class="row" id="avisoRecibido">
                        <div class="input-field col s4">
                            <input type="text" class="timepicker" placeholder="Hora" id="timePickeravisoRecibido">
                            <label for="timePickeravisoRecibido">Hora</label>
                        </div>
                        <div class="col s8">
                            <a class="waves-effect waves-light btn-large col s12">Aviso recibido PS</a>
                        </div>
                    </div>
                    <div class="row" id="avisollegada">
                        <div class="input-field col s4">
                            <input type="text" class="timepicker" placeholder="Hora" id="timePickeravisollegada">
                            <label for="timePickeravisollegada">Hora</label>
                        </div>
                        <div class="col s8">
                            <a class="waves-effect waves-light btn-large col s12">Llegada PS</a>
                        </div>
                    </div>
                    <div class="row" id="avisoHoras">
                        <div class="col s4">
                            <div class="input-field">
                                <input name="horas" id="horas" type="number" placeholder="0.00" class="validate" step=".25">
                                <label for="horas">Horas (1.5)</label>
                            </div>
                        </div>
                        <div class="col s8">
                            <a class="waves-effect waves-light btn-large col s12">Horas Teóricas PS</a>
                        </div>
                    </div>
                    <div class="row" id="avisoFinalizar">
                        <div class="input-field col s4">
                            <input type="text" class="timepicker" placeholder="Hora" id="timePickeravisoFinalizar">
                            <label for="timePickeravisoFinalizar">Hora</label>
                        </div>
                        <div class="col s8">
                            <a class="waves-effect waves-light btn-large col s12">Finalizar PS</a>
                        </div>
                    </div>
                    <div class="row" id="avisoFinalizarPausa">
                        <div class="input-field col s2">
                            <input disabled placeholder="Placeholder" id="inicioPausaHora" type="text" class="validate">
                            <label for="inicioPausaHora">Inicio Pausa</label>
                        </div>
                        <div class="input-field col s2">
                            <input type="text" class="timepicker" placeholder="Hora" id="timePickeravisoFinalizarPausa">
                            <label for="timePickeravisoFinalizarPausa">Final Pausa</label>
                        </div>
                        <div class="col s8">
                            <a class="waves-effect waves-light orange btn-large col s12">Finalizar pausa
                                <span id="tipoPausaText"></span>
                            </a>
                        </div>
                    </div>
                    <div class="row" id="avisoFinalizarServicio">
                        <a class="waves-effect waves-light red btn-large col s12">Finalizar servicio del equipo</a>
                    </div>
                    <div class="row" id="desbloquearEquipo">
                        <a id="desbloquearEquipoBtn" class="waves-effect waves-light green btn-large col s12">Desbloquear equipo</a>
                    </div>
                    <div class="row" id="PSFInalizado">
                        <h5>La PS está finalizada</h5>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <br>
        <div id="confirmarAvisoRecibido" class="modal">
            <div class="modal-content">
                <h4>Cambiar hora de aviso</h4>
                <p>¿Seguro que quieres modificar la hora de aviso?</p>
            </div>
            <div class="modal-footer">
                <a href="#!" class="modal-action modal-close waves-effect waves-red btn-flat">Cerrar</a>
                <a href="#!" class="modal-action modal-close waves-effect waves-green btn red" id="confirmarCambioAvisoRecibido">Confirmar</a>
            </div>
        </div>
        <div id="confirmarAvisoLlegada" class="modal">
            <div class="modal-content">
                <h4>Cambiar hora de llegada</h4>
                <p>¿Seguro que quieres modificar la hora de llegada?</p>
            </div>
            <div class="modal-footer">
                <a href="#!" class="modal-action modal-close waves-effect waves-red btn-flat">Cerrar</a>
                <a href="#!" class="modal-action modal-close waves-effect waves-green btn red" id="confirmarCambioAvisoLlegada">Confirmar</a>
            </div>
        </div>
        <div id="confirmarHorasTeoricas" class="modal">
            <div class="modal-content">
                <h4>Cambiar tiempo teórico</h4>
                <p>¿Seguro que quieres modificar el tiempo teórico?</p>
            </div>
            <div class="modal-footer">
                <a href="#!" class="modal-action modal-close waves-effect waves-red btn-flat">Cerrar</a>
                <a href="#!" class="modal-action modal-close waves-effect waves-green btn red" id="confirmarCambioHorasTeoricas">Confirmar</a>
            </div>
        </div>
        <div id="confirmarAvisoFinalizar" class="modal">
            <div class="modal-content">
                <h4>Cambiar hora de finalización de PS</h4>
                <p>¿Seguro que quieres modificar la hora de finalización de PS?</p>
            </div>
            <div class="modal-footer">
                <a href="#!" class="modal-action modal-close waves-effect waves-red btn-flat">Cerrar</a>
                <a href="#!" class="modal-action modal-close waves-effect waves-green btn red" id="confirmarCambioAvisoFinalizar">Confirmar</a>
            </div>
        </div>
    </main>
    <?php
include 'scripts.php';
}
?>
</body>

</html>