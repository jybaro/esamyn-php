<?php
?>
<html>
<head>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

<link rel="stylesheet" href="/css/bootstrap-datetimepicker.min.css">

<style>
body { padding-top: 70px; }
</style>

</head>
<body>

<nav class="navbar navbar-default  navbar-fixed-top">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">ESAMyN - 
      <?php
//var_dump($_SESSION);
if (isset($_SESSION['ess_nombre'])){
    //echo "Establecimiento de Salud: ". $_SESSION['ess_nombre'];
    echo $_SESSION['ess_nombre'];
}
?></a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Formularios<span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="/main">Mostrar estado del llenado</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/form/1">1. Formulario de Información del Establecimiento</a></li>
            <li><a href="/form/2">2. Formulario de Observación</a></li>
            <li><a href="/form/3">3. Formulario de Encuesta para Madres Gestantes</a></li>
            <li><a href="/form/4">4. Formulario de Encuesta para Madres Puérperas</a></li>
            <li><a href="/form/5">5a. Formulario de Encuesta para Personal de Salud en Contacto con la Madre</a></li>
            <li><a href="/form/6">5b. Formulario de Encuesta para Personal de Salud sin contacto directo con madres</a></li>
            <li><a href="/form/7">6. Formulario de Revisión de Historias Clínicas</a></li>
          </ul>
        </li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Reportes<span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="/evaluacion">Formulario de evaluaci&oacute;n</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/reporteFormularios">Formularios</a></li>
            <li><a href="/reporteEvaluaciones">Evaluaciones</a></li>
          </ul>
        </li>
      </ul>
      <!--form class="navbar-form navbar-left">
        <div class="form-group">
          <input type="text" class="form-control" placeholder="Centro de salud">
        </div>
        <button type="submit" class="btn btn-default">Buscar</button>
      </form-->
      <ul class="nav navbar-nav navbar-right">
        <?php if ($_nivel <= 2): ?>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Administración<span class="caret"></span></a>
          <ul class="dropdown-menu">
            <!--li><a href="/cargarEs">Cargar ES</a></li-->
            <!--li><a href="/CondicionesEs">Condiciones</a></li-->
            <li><a href="/usuarios">Usuarios</a></li>
            <li><a href="/es">Cat&aacute;logos de Establecimientos de Salud</a></li>
            <li><a href="/espaciosEvaluacion">Espacios de evaluaci&oacute;n</a></li>
            <?php if ($_nivel <= 1): ?>
            <li role="separator" class="divider"></li>
            <li><a href="/preguntas">Contenidos</a></li>
            <li><a href="/cargarForm">Cargar cat&aacute;logo de formulario</a></li>
            <?php endif; ?>
            <li role="separator" class="divider"></li>
          </ul>
        </li>
        <?php endif; ?>
        <li class="dropdown">
          <!--a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Opciones de usuario<span class="caret"></span></a-->
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?php
if (isset($_SESSION['cedula'])){
    echo $_SESSION['cedula'];
}
          ?><span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="/infoUsuario">Ver información de usuario</a></li>
            <li><a href="/cambiarClave">Cambiar contraseña</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/login/destroy">Cerrar sesión</a></li>
          </ul>
        </li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>


<div class="modal fade" id="vm_alerta" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Alerta</h4>
            </div>
            <div class="modal-body" id="vm_alerta_mensaje">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Aceptar</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="vm_procesando" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
     aria-hidden="true">

    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <!--img src="img/o.gif"-->
                <div style="width:300px;height:300px;margin:auto;padding-top:140px;background:url('/img/o.gif');background-repeat:no-repeat;background-position: center center;vertical-align: middle;text-align:center;">
                    Procesando <span id="procesando_count"></span>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="/js/form-validator/jquery.form-validator.min.js"></script>
<script src="/js/moment-with-locales.min.js"></script>
<script src="/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<?php echo $content; ?>
</body>
</html>
