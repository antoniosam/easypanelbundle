# Easy Panel Bundle

Bundle de soporte para la creacion de administradores simplificando las tareas sencillas list, new, edit, show(detail)

## Para empezar

El bundle solo proporciona ayuda y soporte para crear platillas para las acciones mas basicas de un panel de administracion, no crea nuevos componentes para el framework, utiliza los componentes de symfony 
Render 
Form Interface
Route
Las consultas para mostrar informacion no se incorporan dentro del bundle, dependen del usuario.

### Installing

Agregar la libreria mediante composer 

```
composer require antoniosam/easypanelbundle
```

Y agregamos el bundle en el AppKernel
```
 $bundles = [
       ...
      new Ast\EasyPanelBundle\EasyPanelBundle(),
      ]
```
## Prerequisitos

No se necesitan mas

## Funcionamiento

EL bundle proporciona un template '@EasyPanel/view.html.twig' el cual permite tener diferentes secciones en una vista.
Se pueden mostrar listas(list), detalles(show), formularios(new y edit) en la misma pantalla 

Existen 4 clases para interactuar con el template
Valores Basicos
**$columnas** = Los campos que se desean visualizar, deben existir dentro de el objeto o la consulta
**$prefix** = Prefix del nombre de la ruta que se podrian generar ej: admin_clientes Generaria admin_clientes_show, admin_clientes_index dependiento del tipo de panel que se cree 
**$layout** por default del prerequisito o si se quiere aplicar un layout direferente

## Servicio

Se creo un nuevo servicio que permite configuracion la vista, incluir un menu personalizado, cambiar el layout general que extiend e la vista, incluir el nombre del proyecto, y mas configuracion.

Sun funcion es sustituir parcialmente a la clase Panel.

Los valores de configuracion son 
```
easy_panel:
    layoutpanel: Layout que extiende la vista (Default: @EasyPanel/layoutmaterial.html.twig)
    viewpanel: es el template que se usa para generar las vistas (Default: @EasyPanel/viewmaterial.html.twig)
    layoutlogin: Es el layout que se usa para mostrar el formulario de sesion (Default: @EasyPanel/loginlayout.html.twig)) 
    viewmenu: Es el template que se incluira en la seccion de menu  (Default: @EasyPanel/Default/menumaterial.html.twig)
    nombreproyecto: Nombre del proyecto (Default: '')
    rutalogout: ruta de symfony para cerrar sesion(Default '')
```

#### Clase EasyForm

Metodos estaticos solo crean una seccion con configuracion basica 
```
EasyForm::easy($titulo_seccion, $form, $prefix = null, $deleteform = null)
```
Para modificar los valores basicos se instancia
```
$form = new EasyForm(titulo_seccion,form)
$form->addLinkBack($route, $parametros, $titulo ) ('btn-secondary', 'fa-arrow-left')
$form->addLinkShow($route, $parametros, $titulo) ('btn-success', 'fa-list-ul')
$form->addLink($route, $parametros, $titulo, $clase = 'btn-secondary', $fa_icon = null)
$form->setDeleteForm($form_delete)
$form->cleanLinks()
```
#### Clase EasyList

Metodos estaticos solo crean una seccion con configuracion basica 
```
EasyForm::easy($titulo_seccion, $consulta, $columnas, $prefix = null)
```
Para modificar los valores basicos se instancia
```
$list = new EasyList($titulo_seccion, $consulta, $columnas);
$list->setCabeceras(array $cabceras)
$list->setNew($route, $parametros, $texto, $clase = 'btn-primary', $fa_icon = 'fa-plus')
$list->tableLinkEdit($route, $parametros, $nombre)
$list->tableLinkShow($route, $parametros, $nombre)
$list->tableLink($route, $parametros, $texto, $clase = 'btn-secondary', $fa_icon = null)
$list->tableCleanLinks()
$list->renderAsImage($columna,$path)
$list->renderAsBoolean($columna)
$list->renderAsDate($columna)
$list->renderAsTime($columna)
$list->renderAsDateTime($columna)
$list->renderAsRaw($columna)
$list->renderAsJson($columna)
$list->renderAsLink($columna,$path)
```
**Version1.3.0**

Se agregan nuevos metodos para personzalizar la vista de tabla 

La opcion *enableSearch* agrega un formulario con el parametro **buscar** para busquedas sencillas(GET) 

El metodo *createListPages* genera una paginacion sencilla con los valores predefinidos a un maximo de 7 elementos con la inclusion de la busqueda si es que esta presente
Los metodos *addPage* *addPages* y *setPageInfo* se usan para una paginacion personalizada
```
$list->enableSearch($value,$textbutton = 'fa-search',$classbutton='btn',$classcontainer='')
$list->addPages(array $array,$info=null)
$list->setPageInfo($pageinfo)
$list->addPage($route,$parameters,$pagina,$class='')
$list->createListPages($totalpages,$currentpage,$search,$route,$classitem,$classactive,$first = "",$last = "")
```
**Version1.4.0**

Ordenamiento por columnas 

La opcion *enableOrder* agrega links para ordenamiento en las cabeceras de las columnas. Por defecto se usan los iconos *fa-sort-asc*, *fa-sort-desc* y *fa-sort* 

```
$list->enableOrder($route,$parametersroute,$ordercolumn = 1,$ordertype = 'ASC');
```

**Version1.5.0**

Se agrega el metodo setFirstColumnCount que cambia el valor de la primera columna y lo reemplaza por el numero de fila que se renderea, se puede establecer un valor de inicio

El metodo addPages ya no recibira la informacion de setPageInfo

```
$list->setFirstColumnCount($initnumber)
$list->addPages(array $array)
``` 
**Version1.6.0**

Se agrega los metodos addLinkEdit, addLinkBack, addLink y cleanLinks que permiten agregar opciones debajo de la lista (card-footer)

```
$list->addLinkEdit( $route, $parametros, $nombre)   ('btn-info', 'fa-edit')
$list->addLinkBack( $route, $parametros,$nombre )   ('btn-secondary', 'fa-arrow-left')
$list->addLink($route, $parametros, $texto,$clase = 'btn-secondary',$fa_icon = null)
$list->cleanLinks()

```
 **Version1.6.1**
 
 **createListPages** Acepta params para generar la ruta
 ```
 $list->createListPages($totalpages,$currentpage,$search,$route,$params,$classitem,$classactive,$first = "",$last = "")
 ```


#### Clase EasyShow

Metodos estaticos solo crean una seccion con configuracion basica 
```
EasyForm::easy($titulo_seccion , $objeto, $columnas, $prefix = null, $deleteform=null)
```
Para modificar los valores basicos se instancia
```
$show = new EasyShow($titulo_seccion, $objeto, $columnas);
$show->setCabeceras(array $cabeceras)
$show->setNew($route, $parametros, $texto, $clase = 'btn-primary', $fa_icon = 'fa-plus')
$show->addLinkEdit( $route, $parametros, $nombre)   ('btn-info', 'fa-edit')
$show->addLinkBack( $route, $parametros,$nombre )   ('btn-secondary', 'fa-arrow-left')
$show->addLink($route, $parametros, $texto,$clase = 'btn-secondary',$fa_icon = null)
$show->cleanLinks()
$show->renderAsImage($columna,$path)
$show->renderAsBoolean($columna)
$show->renderAsDate($columna)
$list->renderAsTime($columna)
$list->renderAsDateTime($columna)
$show->renderAsRaw($columna)
$list->renderAsJson($columna)
$list->renderAsLink($columna,$path)
```

**Notas**

***renderAsImage*** y **renderAsLink**
  
El metodo renderAsImage y renderAsLink permite agregar una ruta para la correcta visualizacion de la imagen o el archivo. 
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
<img src="/uploads/perfil/fotousuario.jpg" alt="Image" class="img-responsive easypanel-img">


...
$vista->renderAsLink('archivo',$this->generateUrl('home_link',['file'=>$name]))
...
```
Tomando en cuenta que el metodo **archivo** devolviera un valor **registro1.pdf** y el metodo generateUrl(Symfony 3 y 4) generaria */descargaregistro/*:
```
<a href="/descargaregistro/registro1.pdf" target="_blank"  class="img-responsive easypanel-link">registro1.pdf</a>
```

### TRANSLATE
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
***renderAsTranslate***

Y por ultimo para poder visulizar la traduccion se creo el metodo renderAsTranslate 
```
$view->renderAsTranslate('translate.titulo');
$view->renderAsTranslate('translate.titulo~en|es');
$view->renderAsTranslate('translate.titulo~en|es|it');
```


***Tablas relacionadas***

Si se tiene una consulta relacionado lo que generaria tener un objeto de la relacion en lugar de un valor definido. Se puede elegir el metodo que desea visualizar el objeto relacionado
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

En la version 1.2.4 Solo se permite 1 nivel de relacion


#### Clase Panel

Metodos estaticos 
Estos metodos solo crean una sola vista del template con configuracion basica
```
Panel::easyList($titulo_seccion, array $consulta, array $columnas, $prefix,$layout=null)
Panel::easyShow($titulo_seccion, $objeto, array $columnas, $prefix,$deleteform = null,$layout=null)
Panel::easyForm($titulo_seccion, $form, $prefix, $deleteform = null,$layout=null)
```
Para crear vistas con configuracion mas personalizada se usan los metodos
```
Panel::createList(EasyList $datos,$layout=null)
Panel::createShow(EasyShow $datos,$layout=null)
Panel::createForm(EasyForm $datos,$layout=null)
```

Si se desea crear mas de una seccion en la vista
Se instancia la clase 
```
$panel = new Panel($layout);
$panel->addForm(EasyForm $datos)
$panel->addList(EasyList $datos)
$panel->addShow(EasyShow $datos)
```
Para convertir la informacion y enviarla a la template se llama el metodo 
```
$panel->createView()
```
Que crea el arreglo con la configuracion lista para el template 
```
@EasyPanel/view.html.twig
```

## Comandos

Para facilitar la creacon del panel se incluyen metodos que generar automaticamente los controladores y formularios con funciones preestablecidas
Los parametros dependen de la version de symfony 
- titulo_proyecto: Nombre del proyecto
- directorio_bundle: Carpeta donde se crearan los archivos, si es symfony 3 debes indicar el bundle y si es symfony 4  creara una carpeta con el nombre establecido dentro de la estructura de symfony
- entity_bundle:  Carpeta donde se encuentran las entidades a controlar
- prefix_name_route: Sufijo para la ruta de los controladores

Los parametros no deben incluir la carpeta src, se integran por default
 
```
easypanel:create:panel titulo_proyecto directorio_bundle entity_bundle prefix_name_route
easypanel:create:menu titulo_proyecto  directorio_bundle entity_bundle prefix_name_route
easypanel:install:assets
```
ej. Symfony 3 
```
easypanel:create:panel Demo AdminBundle AppBundle\Entity admin
```
ej. Symfony 4 
```
easypanel:create:panel Demo Admin Entity admin
```

## Login

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
**Symfony 4**

Si se usa con una version de symfony 4, los comandos de creacion generar la clase **EasyPanelLoginFormAuthenticator**

Dentro de la carpeta src/Security/dir/EasyPanelLoginFormAuthenticator
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
                    - App\Security\dir\EasyPanelLoginFormAuthenticator
```


### Authors

* **Antonio Samano** - *Initial work* - [Antoniosam](https://github.com/antoniosam)

### License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
