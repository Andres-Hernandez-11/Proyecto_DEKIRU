<?php
session_start();

// Inicializamos variables
$formulario = 'login'; 
$datosRegistro = [
    'nombre' => '',
    'apellido' => '',
    'correo' => '',
    'usuario' => ''
];

// ¿Desde qué formulario vino el usuario?
if (isset($_SESSION['formulario'])) {
    $formulario = $_SESSION['formulario'];
    unset($_SESSION['formulario']);
}

// Recuperar datos del formulario de registro (si hubo error)
if (isset($_SESSION['datos_registro'])) {
    $datosRegistro = $_SESSION['datos_registro'];
    unset($_SESSION['datos_registro']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - ERP Rápidos del Altiplano</title>
  <link rel="icon" type="image/png" href="../../IMAGENES/IconoWeb.png" />
  <link rel="stylesheet" href="../VISTA/EstilosLogin.css" />
  <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css"/>
</head>
<body>

<!-- MENSAJE DINÁMICO -->
<?php if (isset($_SESSION['mensaje'])): ?>
  <?php
    $mensaje = $_SESSION['mensaje'];
    $tipo_mensaje = isset($_SESSION['tipo_mensaje']) ? $_SESSION['tipo_mensaje'] : 'success';
    $clase_mensaje = $tipo_mensaje === 'error' ? 'error' : 'success';

    // Limpiar después de usar
    unset($_SESSION['mensaje']);
    unset($_SESSION['tipo_mensaje']);
  ?>
  <div class="mensaje <?php echo $clase_mensaje; ?>">
    <?php echo $mensaje; ?>
  </div>
<?php endif; ?>

<div class="logos">
  <img src="../../IMAGENES/LogoRapidosDelAltiplano.jpg" alt="Rápidos del Altiplano" class="logo" />
  <div class="divider"></div>
  <img src="../../IMAGENES/Logodekiru.jpg" alt="Dekiru" class="logo dekiru" />
</div>

<div class="container">
  <h2 id="form-title">Iniciar Sesión</h2>

  <div class="form-wrapper">
    <!-- Formulario Login -->
    <form id="login-form" class="form visible" method="POST" action="../MODELO/login.php">
      <div class="input-container">
        <i class='bx bx-envelope'></i>
        <input type="text" name="correo" placeholder="Correo electrónico" class="input-field" required />
      </div>
      <div class="input-container">
        <i class='bx bx-lock-alt'></i>
        <input type="password" name="contrasena" placeholder="Contraseña" class="input-field" required />
      </div>
      <div class="remember-container">
        <label><input type="checkbox" name="recordar" /> Recuérdame</label>
        <span class="forgot-password">¿Olvidaste tu contraseña?</span>
      </div>
      <button class="btn" type="submit">Entrar</button>
    </form>

    <!-- Formulario Registro -->
    <form id="register-form" class="form hidden" method="POST" action="../MODELO/register.php">
      <div class="input-container">
        <i class='bx bx-user'></i>
        <input type="text" name="nombre" placeholder="Nombre" class="input-field" required
        value="<?php echo htmlspecialchars($datosRegistro['nombre']); ?>" />
      </div>
      <div class="input-container">
        <i class='bx bx-user'></i>
        <input type="text" name="apellido" placeholder="Apellido" class="input-field" required
        value="<?php echo htmlspecialchars($datosRegistro['apellido']); ?>" />
      </div>
      <div class="input-container">
        <i class='bx bx-envelope'></i>
        <input type="email" name="correo" placeholder="Correo electrónico" class="input-field" required
        value="<?php echo htmlspecialchars($datosRegistro['correo']); ?>" />
      </div>
      <div class="input-container">
        <i class='bx bx-user-circle'></i>
        <input type="text" name="usuario" placeholder="Usuario" class="input-field" required
        value="<?php echo htmlspecialchars($datosRegistro['usuario']); ?>" />
      </div>
      <div class="input-container">
        <i class='bx bx-lock-alt'></i>
        <input type="password" name="password" placeholder="Contraseña" class="input-field" required />
      </div>
      <div class="input-container">
        <i class='bx bx-lock-alt'></i>
        <input type="password" name="confirm_password" placeholder="Confirmar contraseña" class="input-field" required />
      </div>
      <button class="btn" type="submit">Registrarse</button>
    </form>
  </div>

  <button class="btn toggle-btn" id="toggle-btn">Registrarse</button>
</div>

<script>
  const toggleBtn = document.getElementById("toggle-btn");
  const loginForm = document.getElementById("login-form");
  const registerForm = document.getElementById("register-form");
  const formTitle = document.getElementById("form-title");
  const formWrapper = document.querySelector(".form-wrapper");

  function adjustHeight() {
    const visibleForm = loginForm.classList.contains("visible") ? loginForm : registerForm;
    formWrapper.style.height = visibleForm.offsetHeight + "px";
  }

  toggleBtn.addEventListener("click", () => {
    loginForm.classList.toggle("visible");
    loginForm.classList.toggle("hidden");
    registerForm.classList.toggle("visible");
    registerForm.classList.toggle("hidden");

    formTitle.textContent = loginForm.classList.contains("visible") ? "Iniciar Sesión" : "Registrarse";
    toggleBtn.textContent = loginForm.classList.contains("visible") ? "Registrarse" : "Iniciar Sesión";

    setTimeout(adjustHeight, 100);
  });

  const formularioActivo = "<?php echo $formulario; ?>";
  if (formularioActivo === "register") {
    loginForm.classList.remove("visible");
    loginForm.classList.add("hidden");
    registerForm.classList.remove("hidden");
    registerForm.classList.add("visible");
    formTitle.textContent = "Registrarse";
    toggleBtn.textContent = "Iniciar Sesión";
    setTimeout(adjustHeight, 100);
  }

  adjustHeight();
</script>
</body>
</html>
