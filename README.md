# Easy Panel Bundle

Bundle de soporte para la creacion de administradores simplificando las tareas sencillas list, new, edit, show(detail)

## Para empezar

El bundle solo proporciona ayuda y soporte para crear platillas para las acciones mas basicas de un panel de administracion, no crea nuevos componentes para el framework, utiliza los componentes de symfony 
Render 
Form Interface
Route
Las consultas para mostrar informacion no se incorporan dentro del bundle, dependen del usuario.

### Installing

Agregar la libreria al composer json
```
"require": {
      ...
      "antoniosam/easypanelbundle": "1.3.*"
    },
```
Tambien agregamos la direccion del repositorio

```
"repositories": [
      ...
      {
          "type": "vcs",
          "url": "https://github.com/antoniosam/easypanelbundle",
          "no-api": true
      }]
```
Y al final agregamos el bundle en el AppKernel
```
 $bundles = [
       ...
      new Ast\EasyPanelBundle\EasyPanelBundle(),
      ]
```
## Prerequisitos

Template layout.html.twig (app/Resources/views/layout.html.twig)

## Funcionamiento

EL bundle proporciona un template '@EasyPanel/view.html.twig' el cual permite tener diferentes secciones en una vista.
Se pueden mostrar listas(list), detalles(show), formularios(new y edit) en la misma pantalla 

Existen 4 clases para interactuar con el template
Valores Basicos
**$columnas** = Los campos que se desean visualizar, deben existir dentro de el objeto o la consulta
**$prefix** = Prefix del nombre de la ruta que se podrian generar ej: admin_clientes Generaria admin_clientes_show, admin_clientes_index dependiento del tipo de panel que se cree 
**$layout** por default del prerequisito o si se quiere aplicar un layout direferente

###Clase EasyForm

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
###Clase EasyList

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
###Clase EasyShow

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
```

**Notas**

***renderAsImage***
  
El metodo renderAsImage permite agregar una ruta para la correcta visualizacion de la imagen. 
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
<img src="/uploads/perfil/fotousuario.jpg" alt="Image" "class"="img-responsive easypanel-img">
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


###Clase Panel

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
Comandos para la creacion 
```
easypanel:crud titulo_proyecto panel_bundle entity_bundle prefix_name_route
easypanel:create titulo_proyecto panel_bundle entity_bundle prefix_name_route
easypanel:create:sato titulo_proyecto panel_bundle entity_bundle prefix_name_route

easypanel:create:menu titulo_proyecto panel_bundle entity_bundle prefix_name_route

easypanel:export:assets

```
## Authors

* **Antonio Samano** - *Initial work* - [Antoniosam](https://github.com/antoniosam)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
