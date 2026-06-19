# Sistema de Matrícula SAM

Este es un sistema de gestión de matrículas desarrollado con PHP bajo una arquitectura MVC (Modelo-Vista-Controlador). El proyecto está completamente dockerizado para facilitar su configuración, desarrollo y despliegue en cualquier entorno sin necesidad de instalar dependencias locales.

##  Características Principales

* **Autenticación:** Sistema de login seguro (`AuthController`).
* **Gestión de Usuarios:** Módulos dedicados para Estudiantes y Postulantes.
* **Panel de Administración:** Control centralizado del sistema (`AdminController`).
* **Entorno Aislado:** Configuración lista para usarse mediante contenedores de Docker.

##  Requisitos Previos

Asegúrate de tener instalado lo siguiente en tu máquina local:
* [Git](https://git-scm.com/)
* [Docker](https://www.docker.com/products/docker-desktop)
* [Docker Compose](https://docs.docker.com/compose/install/)

##  Instalación y Ejecución

Sigue estos pasos para levantar el proyecto en tu entorno local:

1. **Clona el repositorio:**
   ```bash
   git clone [https://github.com/Ras3x/sistema_matricula_sam.git](https://github.com/Ras3x/sistema_matricula_sam.git)
   cd sistema_matricula_sam
