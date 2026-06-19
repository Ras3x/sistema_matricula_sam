<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instituto de Educación Superior Santiago Antúnez de Mayolo - SAM</title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome Icons CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Estilos Personalizados -->
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 40%, #0f172a 100%);
            color: white;
            padding: 100px 0;
            border-radius: 0 0 50px 50px;
            position: relative;
            overflow: hidden;
        }
        .hero-section::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 40px;
            background: var(--sam-gray-light);
            clip-path: ellipse(60% 40px at 50% 40px);
        }
        .career-card {
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            background: #fff;
        }
        .career-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(14, 165, 233, 0.15);
        }
        .career-icon {
            font-size: 2.5rem;
            color: var(--sam-celeste-dark);
            margin-bottom: 15px;
        }
    </style>
</head>
<body class="bg-light">

    <!-- Navbar Superior -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="assets/img/logo-sam.png" alt="Logo SAM" height="40" class="me-2 p-1">
                <span class="fw-bold tracking-wide">IESTP SAM</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-2">
                        <a href="index.php?route=login" class="btn btn-outline-info text-white me-2 px-4 rounded-pill">
                            <i class="fas fa-sign-in-alt me-1"></i> Ingresar al Sistema
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?route=register" class="btn btn-info text-white px-4 rounded-pill shadow-sm" style="background-color: var(--sam-celeste); border-color: var(--sam-celeste);">
                            <i class="fas fa-user-plus me-1"></i> Proceso de Admisión
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sección Hero -->
    <header class="hero-section text-center animate-fade-in">
        <div class="container py-4">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto">
                    <img src="assets/img/logo-sam.png" alt="Logo SAM" width="120" class="mb-4  p-2  animate-on-scroll">
                    <h1 class="display-4 fw-extrabold mb-3">Portal de Admisiones y Matrículas</h1>
                    <p class="lead mb-4 text-light opacity-90">Bienvenido al sistema del Instituto de Educación Superior Tecnológico Público "Santiago Antúnez de Mayolo". Formando los profesionales técnicos del futuro con excelencia académica.</p>
                    
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="index.php?route=register" class="btn btn-light btn-lg px-4 py-3 rounded-pill fw-bold text-primary shadow-sm hover-up">
                            <i class="fas fa-graduation-cap me-2"></i> Iniciar Inscripción de Admisión
                        </a>
                        <a href="index.php?route=login" class="btn btn-outline-light btn-lg px-4 py-3 rounded-pill fw-semibold hover-up">
                            <i class="fas fa-user-lock me-2"></i> Acceso Estudiantes / Personal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Contenido Principal - Carreras -->
    <main class="container my-5 py-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-dark position-relative d-inline-block pb-2">
                Nuestros Programas de Estudio
                <span style="position: absolute; bottom: 0; left: 25%; width: 50%; height: 4px; background-color: var(--sam-celeste); border-radius: 2px;"></span>
            </h2>
            <p class="text-muted mt-2">Explora las 8 carreras profesionales autorizadas por el Ministerio de Educación que ofrecemos para tu desarrollo profesional.</p>
        </div>

        <div class="row g-4">
            <!-- Carrera 1 -->
            <div class="col-md-6 col-lg-3 animate-on-scroll">
                <div class="card h-100 career-card p-4 text-center">
                    <div class="career-icon"><i class="fas fa-briefcase"></i></div>
                    <h5 class="fw-bold text-dark">Asistencia Administrativa</h5>
                    <p class="text-muted small">Manejo de herramientas gerenciales, control documental y procesos operativos empresariales.</p>
                </div>
            </div>

            <!-- Carrera 2 -->
            <div class="col-md-6 col-lg-3 animate-on-scroll">
                <div class="card h-100 career-card p-4 text-center">
                    <div class="career-icon"><i class="fas fa-calculator"></i></div>
                    <h5 class="fw-bold text-dark">Contabilidad</h5>
                    <p class="text-muted small">Gestión financiera, tributaria, análisis de costos y control contable corporativo.</p>
                </div>
            </div>

            <!-- Carrera 3 -->
            <div class="col-md-6 col-lg-3 animate-on-scroll">
                <div class="card h-100 career-card p-4 text-center">
                    <div class="career-icon"><i class="fas fa-code"></i></div>
                    <h5 class="fw-bold text-dark">Diseño y Programación Web</h5>
                    <p class="text-muted small">Desarrollo de software, diseño de interfaces, bases de datos y administración de servidores.</p>
                </div>
            </div>

            <!-- Carrera 4 -->
            <div class="col-md-6 col-lg-3 animate-on-scroll">
                <div class="card h-100 career-card p-4 text-center">
                    <div class="career-icon"><i class="fas fa-user-md"></i></div>
                    <h5 class="fw-bold text-dark">Enfermería Técnica</h5>
                    <p class="text-muted small">Cuidado integral de la salud, primeros auxilios y asistencia médica intrahospitalaria.</p>
                </div>
            </div>

            <!-- Carrera 5 -->
            <div class="col-md-6 col-lg-3 animate-on-scroll">
                <div class="card h-100 career-card p-4 text-center">
                    <div class="career-icon"><i class="fas fa-pills"></i></div>
                    <h5 class="fw-bold text-dark">Farmacia Técnica</h5>
                    <p class="text-muted small">Expedición de medicamentos, control de stock farmacéutico y atención al cliente.</p>
                </div>
            </div>

            <!-- Carrera 6 -->
            <div class="col-md-6 col-lg-3 animate-on-scroll">
                <div class="card h-100 career-card p-4 text-center">
                    <div class="career-icon"><i class="fas fa-apple-alt"></i></div>
                    <h5 class="fw-bold text-dark">Industrias Alimentarias</h5>
                    <p class="text-muted small">Transformación de alimentos, control de calidad e inocuidad alimentaria industrial.</p>
                </div>
            </div>

            <!-- Carrera 7 -->
            <div class="col-md-6 col-lg-3 animate-on-scroll">
                <div class="card h-100 career-card p-4 text-center">
                    <div class="career-icon"><i class="fas fa-cogs"></i></div>
                    <h5 class="fw-bold text-dark">Mecánica de Producción</h5>
                    <p class="text-muted small">Mecanizado de piezas, metalmecánica, mantenimiento industrial y soldadura técnica.</p>
                </div>
            </div>

            <!-- Carrera 8 -->
            <div class="col-md-6 col-lg-3 animate-on-scroll">
                <div class="card h-100 career-card p-4 text-center">
                    <div class="career-icon"><i class="fas fa-tractor"></i></div>
                    <h5 class="fw-bold text-dark">Producción Agropecuaria</h5>
                    <p class="text-muted small">Tecnología agrícola, crianza pecuaria sostenible, gestión de fundos y agronegocios.</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-1 fw-semibold">&copy; 2026 Instituto de Educación Superior Tecnológico Público Santiago Antúnez de Mayolo</p>
            <p class="text-muted small mb-0">Huancayo, Junín, Perú | Diseñado profesionalmente como una solución integral de matrícula</p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Script Personalizado -->
    <script src="assets/js/main.js"></script>
</body>
</html>
