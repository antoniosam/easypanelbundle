# Easy Panel Bundle Symfony 5

###**Se elimino todo el soporte para symfony 4 y 3**

#

Bundle de soporte para la creacion de administradores simplificando las tareas sencillas list, new, edit, show(detail)

## Para empezar

El bundle solo proporciona ayuda y soporte para crear platillas para las acciones mas basicas de un panel de administracion, no crea nuevos componentes para el framework, utiliza los componentes de symfony

- Render (twig)
- Form Interface
- Route
  Las consultas para mostrar informacion no se incorporan dentro del bundle, dependen del usuario.

### Instalacion

```
composer require antoniosam/easypanelbundle
```

Y agregamos el bundle en el AppKernel

```
 $bundles = [
       Ast\EasyPanelBundle\EasyPanelBundle::class => ['all' => true],
       ...
      ]
```

## Comandos Crud

Para facilitar la creacon del panel se incluyen metodos que generar automaticamente los controladores y formularios con funciones preestablecidas, puede ser todas las Entidades o una por una

**Opcionales**

- folder: Carpeta que se creara dentro de las carpetas de la estructura del proyecto
- prefix:Sufijo opcional para la ruta de los controladores (default:empty)
  Los parametros no deben incluir la carpeta src, se integran por default

### create:panel

Busca todas las entidades dentro de la carpeta indicada y crea todos los controladores y formularios

```
php bin/console easypanel:create:panel nombre_proyecto directorio_entitys tipo
php bin/console easypanel:create:panel "Admin Mascotas" Entity(equal = /src/Entity) html
php bin/console easypanel:create:panel Demo Entity html --folder=admin --prefix=admin
php bin/console easypanel:create:panel Demo Entity api --folder=api --prefix=api
php bin/console easypanel:create:panel "Admin Mascotas" Entity --clase_login=usuario
ej.
php bin/console easypanel:create:panel "Admin Mascotas" Entity html --folder=admin --prefix=admin --clase_login=usuario
```

### create:menu

Crea un archivo Twig que incluye todas las rutas de las entidades que se encontraron para poder importarla en la configuracion

```
php bin/console easypanel:create:menu Demo Entity
```

### create:modulo

Selecciona una entidad por su **namespace** y crea su controlador y su formulario

```
php bin/console easypanel:create:modulo App\Entity\Mascota
php bin/console easypanel:create:modulo App\Entity\Mascota api --folder=api --prefix=api
Output
/project_root/src/Controller/MascotaController.php
/project_root/src/Controller/Api/MascotaController.php
```

### install:assets

Descomprime 1 archivos Zip que contienen los recursos css y js para el panel(En las configuraciones se cambia el tipo de panel)

```
easypanel:install:assets --tipo=material o  sb-admin
```

### Login

**NOTA** Se recomienda primero crear el admnistrador antes de establecer la seguridad

El bundle solo incluye las pantallas y los controladores, la confifuracion de seguridad se debe hacer en el archivo security.yml

El servicio app.custom_encoder esta incluido dentro del bundle

security.yml

```
security:
    encoders:
        App\Entity\Administrador:
            id: app.custom_encoder
            ...
    providers:
        entity_admin:
            entity:
                class:  App\Entity\Administrador
                property: correo
        ...
    firewalls:
        admin_area:
            pattern: /admin.*
            provider: entity_admin
            anonymous: ~
            form_login:
                login_path: /admin/login/
                check_path: /admin/login/
                default_target_path:  /admin/
            logout:
                path:   /admin/login/salir
                target: /admin/

        main:
           ....
    access_control:
        - { path: ^/admin/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/admin/*,       roles: IS_AUTHENTICATED_FULLY }
        ....
```

En comando **create:panel** genera la clase **EasyPanelLoginFormAuthenticator**

Dentro de la carpeta src/Security/[**dir**]/EasyPanelLoginFormAuthenticator
y se debe incluir en el firewall

**dir** es el nombre de la carpeta que se uso en el comando

ej.

```
    firewalls:
        admin_area:
            pattern: /admin.*
            provider: entity_admin
            anonymous: ~
            form_login:
                login_path: /admin/login/
                check_path: /admin/login/
                default_target_path:  /admin/
            logout:
                path:   /admin/login/salir
                target: /admin/
            guard:
                authenticators:
                    - App\Security\[dir]\EasyPanelLoginFormAuthenticator
```

## Servicio

Permite configuracion la vista, incluir un menu personalizado, cambiar el layout general(Al que Extiende la vista ), incluir el nombre del proyecto, y mas configuracion.

```
easy_panel:
    layoutpanel: Layout que extiende la vista (Default: @EasyPanel/layoutmaterial.html.twig)
    viewpanel: es el template que se usa para generar las vistas (Default: @EasyPanel/viewmaterial.html.twig)
    layoutlogin: Es el layout que se usa para mostrar el formulario de sesion (Default: @EasyPanel/loginlayout.html.twig))
    viewmenu: Es el template que se incluira en la seccion de menu  (Default: @EasyPanel/Default/menumaterial.html.twig)
    nombreproyecto: Nombre del proyecto (Default: '')
    rutalogout: ruta de symfony para cerrar sesion(Default '')
```

Este servicio se utiliza para gestionar las vistas, hacer los renders si se usa el metodo **render** o parsear la respuesta si se usa **json**

**NOTA** Cuando se usa **json** solo funciona para **EasyList** y **EasyShow** y no se pueden agrupar 2 o mas vistas

### Clases

#### EasyForm

Crea una vista que integra un formulario, El formulario debe ser creado anteriormente

```
$form = $this->createForm(MascotaType::class, new Mascota())->createView();
$easyForm = EasyForm::easy('Agregar Masctota', $form);
```

Para agregar mas opciones al formulario

```
$easyForm->addLinkBack($route, $parametros, $titulo ) ('btn-secondary', 'fa-arrow-left')
$easyForm->addLinkShow($route, $parametros, $titulo) ('btn-success', 'fa-list-ul')
$easyForm->addLink($route, $parametros, $titulo, $clase = 'btn-secondary', $fa_icon = null)
$easyForm->setDeleteForm($form_delete)
$easyForm->cleanLinks()
```

El servicio EasyPanel es el encargado de mandar la informacion a la platilla y twig se encarga del render

```
return $easypanel->render($easyForm);
```

Para configurar el formulario es necesario ir al archivo Type y hacer las validaciones y configuraciones necesarias

#### EasyList

Este clase tiene dependencia directa de plugin **EasyDoctrine** y la clase **EasyData**

Esta clase permite generar una tabla(lista) de una consulta y mostrar los campos establecido. Agregar paginacion, busqueda, ordenamiento y seleccion de items

```
$easyList = EasyForm::easy('Lista de mascotas', EasyData $easyData, ['id','nombre','creado']);
$easyList->setLabelsTable(['ID','Mascota','Registrado']);
```

**setLabelsTable** solo tiene efecto en la vista **html**

Se puede cambiar entre una respuesta json y html mediante el servicio $easypanel

**HTML**

```
return $easypanel->render($easyList);
```

**API**

```
return $easypanel->json($easyList);
```

#### EasyShow

Permite generar una tabla con el detalle de un objeto(show)

```
$easyShow = EasyForm::easy('Ver Mascota', $objeto, ['id','nombre','raza','propietario']);
$easyShow->setLabelsFields(['ID','Nombre de la Mascota','Raza','Propietario']);
```

**setLabelsFields** solo tiene efecto en la vista **html**

Al igual que EasyList se puede cambiar entre una respuesta json y html mediante el servicio $easypanel

**HTML**

```
return $easypanel->render($easyShow);
```

**API**

```
return $easypanel->json($easyShow);
```

#### Configuracion de Respuesta EasyShow y EasyList

Para poder dar flexibilidad a la creacion de vistas y respuestas json estas dos clases permiten el uso de multiples metodos

**RENDER AS**

Metodos que permiten modificar el valor un campo obtenido de la consulta y mostrarlo en un formato diferente

Valido en metodo **json** y **render**

```
$list->renderAsText('nombre'); <p>Popi</p> Default para todos los campos
$list->renderAsImage('foto','uploads/images'); domain.com/asssets/uploads/images/perro.jpg
$list->renderAsBoolean('activo'); <i class="fa fa-check"></i> <i class="fa fa-times"></i>
$list->renderAsDate('nacimiento'); 2020-01-01
$list->renderAsTime('consulta_hora'); 12:00:00
$list->renderAsDateTime('creacion'); 2021-01-01 09:00:00
$list->renderAsRaw('descripcion'); <p>Parrafo generado con algun editor web</p>
$list->renderAsJson('Etiquetas'); <ul><li>Pequeña</li><li>Cachorro</li></ul>
$list->renderAsLink('certificado','uploads/docs'); domain.com/asssets/uploads/docs/CErtificado.pdf
$list->renderAsTranslate('cv'); (detalle acontinuacion)
```

**renderAsImage** y **renderAsLink**

El metodo renderAsImage y renderAsLink permite agregar una ruta para la correcta visualizacion de la imagen o el archivo.

Si en la ruta que se proporciona ya incluye el nombre del archivo se usa esa por defecto, si no lo incluye se contruye con el valor de path y el valor del archivo
`$path.'/'.$valor`

Si se ignora solo se antepone '/' para marcar la raiz del sitio

```
$manager = $this->get('assets.packages');
$manager->getUrl('comprobantes'));

...
$vista->renderAsImage('fotoperfil',$manager->getUrl('uploads/perfil'))
...
```

Tomando en cuenta que el metodo **fotoperfil** devolviera un valor **fotousuario.jpg** el resultado html seria:

```
$vista->renderAsImage('fotoperfil',$manager->getUrl('uploads/perfil'))
...
<img src="/uploads/perfil/fotousuario.jpg" alt="Image" class="img-responsive easypanel-img">
```

Para el metodo renderAslink se sugiere utilizar el metodo **generateUrl** de symfony

Tomando en cuenta que el metodo **archivo** devolviera un valor **registro1.pdf** y tomando un ejemplo de de ruta `file_preview` con la configuracion `/vista/{archivo}/preview` obtendriamos

```
$vista->renderAsLink('fotoperfil',$this->generateUrl('file_preview',['archivo'=>$objeto->getArchivo()]))
...
<a href="/vista/registro1.pdf/preview" target="_blank"  class="img-responsive easypanel-link">registro1.pdf</a>
```

Si no se incluye el nombre del archivo en la ruta generada solo se incluye al final

```
$vista->renderAsLink('fotoperfil','/vista-archivo/preview')
...
<a href="/vista-archivo/preview/registro1.pdf" target="_blank"  class="img-responsive easypanel-link">registro1.pdf</a>
```

#### TRANSLATE

Para aplicar la traduccion este bundle se basa en la configuracion de **knplabs/doctrine-behaviors**

https://github.com/KnpLabs/DoctrineBehaviors

Para poder aplicarlo solo se debe configurar la columna

```
[...,'translate.titulo',...]
```

Por default la traduccion sera con el parametro locale de la clase Request de Symfony

Para poder ver varias traducciones al mismo tiempo se agregan los idiomas a la columna

```
[...,'translate.titulo~en|es',...]
[...,'translate.titulo~en|es|it',...]
```

**_renderAsTranslate_**

Y por ultimo para poder visulizar la traduccion se creo el metodo renderAsTranslate

```
$view->renderAsTranslate('translate.titulo');
$view->renderAsTranslate('translate.titulo~en|es');
$view->renderAsTranslate('translate.titulo~en|es|it');
```

#### RELACIONES

Si se tiene una consulta relacionada se puede elegir el metodo que desea visualizar el objeto relacionado

```
$columnas=['userid', 'username','usertask.name'];
```

Los metodos **userid** y **username** imprimirian el valor correspondiente para **usertask** el metodo internamente comprueba que la relacion no devuelda un valor**Null** y despues hace el llamado. Internamente la ejecucion seria la siguiente:

```
$relacion = $objeto->getUsertask()
if($relacion!=null){
    return $relacion->getName();
}else{
    return '';
}
```

En la version 3 se permite una relacion hasta 2 niveles

```
$columnas=['userid', 'username','localidad.municipio.nombre'];
$usuario->getLocalidad()->getMunicipio()->getNombre()
```

#### EasyList Configuracion

Para poder agregar usabilidad a la vista de Lista se tiene diferentes metodos

**RUTAS**

Se tiene que usar rutas registradas por symfony en el proyecto

```
$list->tableLinkEdit($route, $parametros, $nombre)
$list->tableLinkShow($route, $parametros, $nombre)
$list->tableLink($route, $parametros, $texto, $clase = 'btn-secondary', $fa_icon = null)
$list->tableCleanLinks()
```

**ORDERNAMIENTO y BUSQUEDA**

```php
$list->enableSearch($value,$textbutton = 'fa-search',$classbutton='btn',$classcontainer='')
$list->enableOrder($route,$parametersroute,$ordercolumn = 1,$ordertype = 'ASC');
```

**PAGINACION**

```php
$list->setPageInfo($pageinfo);
$list->createListPages($search,$route,$classitem,$classactive,$first = "",$last = "");
```

**CONTADOR DE FILA**

```php
    $list->setFirstColumnCount(true);
```

**NOTA** solo funciona cuando la seccion se muestra como **html**

#### GLOBAL: Links el final de la vista

En los 3 tipos de clases se permite el ingreso de links al final de la seccion

La ruta tiene que ser una ruta definida en el proyecto por symfony

```
$list->addLinkEdit( $route, $parametros, $nombre)   ('btn-info', 'fa-edit')
$list->addLinkBack( $route, $parametros,$nombre )   ('btn-secondary', 'fa-arrow-left')
$list->addLink($route, $parametros, $texto,$clase = 'btn-secondary',$fa_icon = null)
$list->cleanLinks()
```

**NOTA** solo funciona cuando la seccion se muestra como **html**

### Enconder Symfony

Se agrega la clase CustomEnconder como serivicio en el archio services.yml y ese servicio se vincula con la tabla destino usando el encoder del archivo security.yml

```
(services.yml)
    app.my_custom_encoder:
        class: Ast\EasyPanelBundle\CustomPassword\CustomEnconder


(security.yml)
    security:
        encoders:
          AppBundle\Entity\User:
              id: app.my_custom_encoder


```

### Clase BuildPassword

Si se necesita crear la contraseña y no se puede acceder al encoder la clase BuildPassword puede generar las contraseñas con el mismo resultado que el encoder

```
$bp = new BuildPassword()
$bp->generate($pass,BuildPassword::randomSalt());
$hashpass = $bp->getPass();
$salt = $bp->getSalt();

BuildPassword::create($pass,$salt)



### Authors

* **Antonio Samano** - *Initial work* - [Antoniosam](https://github.com/antoniosam)

### License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
```
