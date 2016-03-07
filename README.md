![Logo](http://www.placso.com/zrad-logo.png)

# Zrad Generador de código PHP

Zrad tiene como principal objetivo automatizar la creación y actualización de procesos base (crear, editar, eliminar) de cada entidad definida en el modelo de datos, permitiendo que las tareas habituales, se realicen automáticamente generando archivos con código destinado a resolver un determinado tipo de problemas de aparición recurrente, permitiendo:

**"Disminuir 70% de tiempo en desarrollo"**

Para lograr ese objetivo se apoya de **Zend Framework** y su herramienta CLI para crear diversos componentes. Para ver tutoriales y videos visite la web de [www.zend-rad.com](http://www.zend-rad.com/)

## Funciones

### Automatización de procesos base (CRUD)

Zrad contiene una serie de comando que te ayudan a automatizar varios procesos: desde crear la arquitectura base de un proyecto Zend Framework hasta la creacion de los modelos, backends, frontends y módulos Facebook. Funciones:

- **Crear Proyecto**: Módulos, Conexión a Base de Datos, Google Analytics (GA), Test, Debug, Documentación, Proyecto en NetBeans, Layouts, Internacionalización
- **Crear Formulario: Crea un Formulario (Clase) cuyos elementos son los campos de la tabla asociada. Zrad contiene una serie de directivas de mapeo que se agregaran como Validaciones y Filtros en cada elemento creado, también puedes crear formularios con captcha
- **Crear Formulario Frontend/Backend**: Ejecuta el comando "Crear Formulario" y adiciona una vista HTML y validación Javascript con jQuery Validate, además crea o usa un controlador para procesar los datos enviados.
- **Crear Model**o: Zend Framework recomienda usar el patrón "Data Mapper" para representar sus modelos, siguiendo este concepto Zrad crea los 3 elementos: Domain Object, Mapper y el DbTable y no sólo los crea sino que realiza un Mapeo total de las Tablas Relacionadas, agregándolos en la definición del DbTable.
- **Crear Crud Frontend**: Ejecuta los comando "Crear Formulario" y "Crear Modelo" además de crear los Módulos (Vistas, Controladores y Modelos) necesarios para orquestar el flujo de registro de cualquier entidad de la Base de Datos.
- **Crear Módulo**: Crear todos los recursos necesarios para crear un módulo además de registrarlo en el Bootstrap para obtener los modelos y formularios mediante Autoloader
- **Iniciar Backend**: Inicializa el backend creando un módulo administrador con un login simple el diseño del backend esta basado en jQuery UI usando Zrad CSS Framework
- **Crear Crud Backend**: Ejecuta los comandos "Crear Formulario Backend", "Crear Modelo", "Crear Modulo" creando las vistas para listar, crear, editar y eliminar todo bajo el framework jQuery UI.
- **Iniciar Facebook**: Crea una estructura de proyecto basada en la propuesta: "Arquitectura para Proyectos Facebook" bajo Zend Framework, en ella se detallan las acciones de "Me Gusta", "Registro", "Fin de Registro", "Fin de Campaña", etc.
- **Iniciar Ubigeo**: Ubigeo son las siglas oficiales para Código de Ubicación Geográfica que usa el INEI para codificar las circunscripciones territoriales del Perú. Este comando crea la tabla y su contenido para poder usarlos para proyectos que serán consumidos en PERÚ
- **Actualizar Recursos**: Todos los comandos de Zrad tienen la opción de actualizar, por ejemplo: Si durante la fase de desarrollo cambian los requerimientos y por ende cambian los nombres y campos de uno o más tablas de tu base de datos Zrad actualiza tus modelos y formularios, ¿Y mis funciones que he creado en mis modelos se perderán?. NO ya que Zrad usa Reflection e identifica los nuevos métodos y no los elimina. :)

### Actualiza tu aplicación desde tu Base de Datos

Zrad identifica los campos nuevos o modificados y actualiza los modelos vistas y controlador para sincronizar la base de datos con el proyecto. Use los comandos: update-model, update-crud-backend y update-form para actualizar sus modelos

### NetBeans

NetBeans IDE es uno de los proyectos de Oracle que apoya continuamente a la comunidad PHP por ello Zrad permite que los proyectos creados se puedan abrir con NetBeans además de poder integrar los comandos al Run Zend Command de NetBeans.

### Zrad Aid

Zrad Aid es una librería de complemento para tus proyectos con Zend Framework esta librería contiene clases de ayuda para todos tu componentes Zend como: elementos, filtros y validaciones, procesamiento de imágenes, procesamiento PDF personalizados (margin, padding, texto multilínea, etc.), Helpers para manejo de Cadenas (UTF8, Tildes, Ñs y más) y manejo de Fechas.
Para más información visita [ZradAid](https://github.com/minayaleon/zrad-aid)

## Instalación

Requerimientos:

- PHP 5.3 ó superior
- MySQL 5 ó superior
- Zend Framework 1
- Zend Tool

Pasos:

- Descargue Zrad y [ZradAid](https://github.com/minayaleon/zrad-aid)
- Ubiquese en el directorio donde esta instalado Zend (puedes ver el include_path en info.php) y descomprime el Zip descargado en el paso anterior. Nota: ZradAid también debe de estar en este directorio, quedadno de la siguiente manera
- Abra nuevamente el terminal del SO y escriba el siguiente comando "zf --setup config-file" y presione enter

```
zf --setup config-file
Config file written to \Users\Juan/.zf.ini
```

- Ahora escriba el siguiente comando `zf enable config.provider Zrad_Tool_Project_Provider_Zrad` y presione enter

```
zf enable config.provider Zrad_Tool_Project_Provider_Zrad
Provider/Manifest 'Zrad_Tool_Project_Provider_Zrad' was enabled for usage with Zend Tool.
```

## Comandos

Para ver todas las funciones disponibles:
```
zf ?
Zend Framework Command Line Console Tool
Usage:
    zf [--global-opts] action-name [--action-opts] provider-name [--provider-opts] [provider parameters ...]
    Note: You may use "?" in any place of the above usage string to ask for more specific help information.
    Example: "zf ? version" will list all available actions for the version provider.
 
Providers and their actions:
  Zrad
    zf create-project zrad name
    zf create-form-backend zrad table-name name in-captcha[=false] module[=admin]
    zf create-form zrad table-name name module in-captcha[=false]
    zf create-form-frontend zrad table-name name module controller action[=index] in-captcha[=false]
    zf update-form-frontend zrad table-name name module controller action-name in-captcha[=false]
    zf create-model zrad table-name module-name
    Note: There are specialties, use zf create-model zrad.? to get specific help on them.
    zf update-model zrad table-name module-name
    zf init-backend zrad
    zf init-facebook zrad
    zf init-ubigeo zrad
    zf create-module zrad name
    zf create-crud-backend zrad table-name module in-captcha[=false] generate-form[=1]
    zf update-crud-backend zrad table-name module in-captcha[=false] generate-form[=1]
    zf create-crud-frontend zrad table-name module in-captcha[=1] generate-form[=1]
```

## Licencia MIT

Copyright (c) 2016 Juan Minaya León

Se concede permiso por la presente, de forma gratuita, a cualquier persona
que obtenga una copia de este software y de los archivos de documentación
asociados (el "Software"), para utilizar el Software sin restricción,
incluyendo sin limitación los derechos de usar, copiar, modificar, fusionar,
publicar, distribuir, sublicenciar, y/o vender copias de este Software, y
para permitir a las personas a las que se les proporcione el Software a
hacer lo mismo, sujeto a las siguientes condiciones:

El aviso de copyright anterior y este aviso de permiso se incluirán en todas
las copias o partes sustanciales del Software.

EL SOFTWARE SE PROPORCIONA "TAL CUAL", SIN GARANTÍA DE NINGÚN TIPO, EXPRESA
O IMPLÍCITA, INCLUYENDO PERO NO LIMITADO A GARANTÍAS DE COMERCIALIZACIÓN,
IDONEIDAD PARA UN PROPÓSITO PARTICULAR Y NO INFRACCIÓN. EN NINGÚN CASO LOS
AUTORES O TITULARES DEL COPYRIGHT SERÁN RESPONSABLES DE NINGUNA RECLAMACIÓN,
DAÑOS U OTRAS RESPONSABILIDADES, YA SEA EN UN LITIGIO, AGRAVIO O DE OTRO MODO,
QUE SURJA DE O EN CONEXIÓN CON EL SOFTWARE O EL USO U OTRO TIPO DE ACCIONES EN
EL SOFTWARE.


