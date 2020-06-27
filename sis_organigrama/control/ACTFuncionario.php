<?php
/***
Nombre: ACTFuncionario.php
Proposito: Clase de Control para recibir los parametros enviados por los archivos
de la Vista para envio y ejecucion de los metodos del Modelo referidas a la tabla tfuncionario
Autor:	Kplian
Fecha:	01/07/2010
 */
require_once(dirname(__FILE__).'/../reportes/RDocumentoRRHHXls.php');

class ACTFuncionario extends ACTbase{

    function listarFuncionario(){

        //el objeto objParam contiene todas la variables recibidad desde la interfaz

        // parametros de ordenacion por defecto
        $this->objParam->defecto('ordenacion','PERSON.nombre_completo2');
        $this->objParam->defecto('dir_ordenacion','asc');
        //$this->objParam->addFiltro("FUNCIO.estado_reg = ''activo''");


        //si aplicar filtro de usuario, fitlramos el listado segun el funionario del usuario
        if($this->objParam->getParametro('tipo_filtro')=='usuario'){
            $this->objParam->addFiltro("FUNCIO.id_funcionario= ".$_SESSION["_ID_FUNCIOANRIO_OFUS"]);
        }

        if( $this->objParam->getParametro('es_combo_solicitud') == 'si' ) {
            $this->objParam->addFiltro("FUNCIO.id_funcionario IN (select *
										FROM orga.f_get_funcionarios_x_usuario_asistente(now()::date, " .
                $_SESSION["ss_id_usuario"] . ") AS (id_funcionario INTEGER)) ");
        }



        if($this->objParam->getParametro('estado_func')=='activo'){
            $this->objParam->addFiltro("(FUNCIO.estado_reg = ''activo'' and current_date <= coalesce (tuo.fecha_finalizacion, ''31/12/9999''::date))");
        }else if($this->objParam->getParametro('estado_func')=='inactivo'){
            $this->objParam->addFiltro("(FUNCIO.estado_reg = ''inactivo'' or tuo.fecha_finalizacion < current_date)");
        }else if($this->objParam->getParametro('estado_func')=='act_desc'){
            $this->objParam->addFiltro("(
            FUNCIO.estado_reg in (''activo'', ''inactivo'') or (current_date <= coalesce (tuo.fecha_finalizacion, ''31/12/9999''::date) or
            tuo.fecha_finalizacion < current_date)
            )");
        }else if($this->objParam->getParametro('estado_func')=='sin_asignacion'){
            $this->objParam->addFiltro("tuo.id_funcionario is null");
        }else{
            $this->objParam->addFiltro("(FUNCIO.estado_reg = ''activo'' and current_date <= coalesce (tuo.fecha_finalizacion, ''31/12/9999''::date))");
        }


        //(FEA)Filtro Correos
        if($this->objParam->getParametro('correo_func')=='sin_correo'){
            $this->objParam->addFiltro("(FUNCIO.email_empresa = '''' or FUNCIO.email_empresa is null)");
        }else if($this->objParam->getParametro('correo_func')=='con_correo'){
            $this->objParam->addFiltro("(FUNCIO.email_empresa != '''' or FUNCIO.email_empresa is not null)");
        }

        //crea el objetoFunSeguridad que contiene todos los metodos del sistema de seguridad
        if ($this->objParam->getParametro('tipoReporte')=='excel_grid' || $this->objParam->getParametro('tipoReporte')=='pdf_grid'){
            $this->objReporte=new Reporte($this->objParam, $this);
            $this->res=$this->objReporte->generarReporteListado('MODFuncionario','listarFuncionario');
        }
        else {
            $this->objFunSeguridad=$this->create('MODFuncionario');
            //ejecuta el metodo de lista funcionarios a travez de la intefaz objetoFunSeguridad
            $this->res=$this->objFunSeguridad->listarFuncionario($this->objParam);

        }

        if($this->objParam->getParametro('todos')!=''){

            $respuesta = $this->res->getDatos();


            array_unshift ( $respuesta, array(  'id_funcionario'=>'0',
                'desc_person'=>'Todos',
                'ci'=>'Todos',
                'documento'=>'Todos',
                'telefono'=>'Todos') );
            $this->res->setDatos($respuesta);
        }



        //imprime respuesta en formato JSON para enviar lo a la interface (vista)
        $this->res->imprimirRespuesta($this->res->generarJson());



    }

    function listarSinAsignacionFuncionario(){

        //el objeto objParam contiene todas la variables recibidad desde la interfaz

        // parametros de ordenacion por defecto
        $this->objParam->defecto('ordenacion','PERSON.nombre_completo2');
        $this->objParam->defecto('dir_ordenacion','asc');
        //$this->objParam->addFiltro("FUNCIO.estado_reg = ''activo''");


        //crea el objetoFunSeguridad que contiene todos los metodos del sistema de seguridad
        if ($this->objParam->getParametro('tipoReporte')=='excel_grid' || $this->objParam->getParametro('tipoReporte')=='pdf_grid'){
            $this->objReporte=new Reporte($this->objParam, $this);
            $this->res=$this->objReporte->generarReporteListado('MODFuncionario','listarSinAsignacionFuncionario');
        }
        else {
            $this->objFunSeguridad=$this->create('MODFuncionario');
            //ejecuta el metodo de lista funcionarios a travez de la intefaz objetoFunSeguridad
            $this->res=$this->objFunSeguridad->listarSinAsignacionFuncionario($this->objParam);
        }
        //imprime respuesta en formato JSON para enviar lo a la interface (vista)
        $this->res->imprimirRespuesta($this->res->generarJson());
    }

    function getDatosFuncionario(){

        //el objeto objParam contiene todas la variables recibidad desde la interfaz

        // parametros de ordenacion por defecto
        $this->objParam->defecto('ordenacion','PERSON.nombre_completo1');
        $this->objParam->defecto('dir_ordenacion','asc');
        $this->objParam->defecto('puntero','0');
        $this->objParam->defecto('cantidad','50');
        $this->objParam->addFiltro("FUNCIO.estado_reg = ''activo''");


        //si aplicar filtro de usuario, fitlramos el listado segun el funionario del usuario
        if($this->objParam->getParametro('id_funcionario')!=''){

            $this->objParam->addFiltro("FUNCIO.id_funcionario = ".$this->objParam->getParametro('id_funcionario')." ");
        }

        if($this->objParam->getParametro('id_persona') != ''){
            $this->objParam->addFiltro("FUNCIO.id_persona= ".$this->objParam->getParametro('id_persona'));
        }

        //si aplicar filtro de usuario, fitlramos el listado segun el funionario del usuario
        if($this->objParam->getParametro('nombre_empleado')!=''){
            $nombre_empleado = trim($this->objParam->getParametro('nombre_empleado'));
            $nombre_empleado = str_replace(' ', '%', $nombre_empleado);
            $this->objParam->addFiltro("(lower(PERSON.nombre_completo1) like lower(''%" .  $nombre_empleado ."%'') or
            							lower(PERSON.nombre_completo2) like lower(''%" .  $nombre_empleado ."%'') or
            							lower(CAR.nombre) like lower(''%" .  $nombre_empleado ."%''))");
        }

        //crea el objetoFunSeguridad que contiene todos los metodos del sistema de seguridad
        if ($this->objParam->getParametro('tipoReporte')=='excel_grid' || $this->objParam->getParametro('tipoReporte')=='pdf_grid'){
            $this->objReporte=new Reporte($this->objParam, $this);
            $this->res=$this->objReporte->generarReporteListado('MODFuncionario','getDatosFuncionario');
        }
        else {
            $this->objFunSeguridad=$this->create('MODFuncionario');
            //ejecuta el metodo de lista funcionarios a travez de la intefaz objetoFunSeguridad
            $this->res=$this->objFunSeguridad->getDatosFuncionario($this->objParam);

        }

        //imprime respuesta en formato JSON para enviar lo a la interface (vista)
        $this->res->imprimirRespuesta($this->res->generarJson());



    }

    function getCumpleaneros(){

        //el objeto objParam contiene todas la variables recibidad desde la interfaz

        // parametros de ordenacion por defecto
        $this->objParam->defecto('ordenacion','FUNCIO.desc_funcionario1');
        $this->objParam->defecto('dir_ordenacion','asc');
        $this->objParam->addFiltro("FUNCIO.estado_reg = ''activo''");


        //si aplicar filtro de usuario, fitlramos el listado segun el funionario del usuario
        if($this->objParam->getParametro('dia')!=''){
            $this->objParam->addFiltro("extract (day from PERSON.fecha_nacimiento) = " . $this->objParam->getParametro('dia'));
        }

        if($this->objParam->getParametro('mes')!=''){
            $this->objParam->addFiltro("extract (month from PERSON.fecha_nacimiento) = " . $this->objParam->getParametro('mes'));
        }

        //crea el objetoFunSeguridad que contiene todos los metodos del sistema de seguridad
        if ($this->objParam->getParametro('tipoReporte')=='excel_grid' || $this->objParam->getParametro('tipoReporte')=='pdf_grid'){
            $this->objReporte=new Reporte($this->objParam, $this);
            $this->res=$this->objReporte->generarReporteListado('MODFuncionario','getCumpleaneros');
        }
        else {
            $this->objFunSeguridad=$this->create('MODFuncionario');
            //ejecuta el metodo de lista funcionarios a travez de la intefaz objetoFunSeguridad
            $this->res=$this->objFunSeguridad->getCumpleaneros($this->objParam);

        }

        //imprime respuesta en formato JSON para enviar lo a la interface (vista)
        $this->res->imprimirRespuesta($this->res->generarJson());



    }

    function listarFuncionarioCargo(){

        //el objeto objParam contiene todas la variables recibidad desde la interfaz
        // parametros de ordenacion por defecto
        $this->objParam->defecto('ordenacion','descripcion_cargo');
        $this->objParam->defecto('dir_ordenacion','asc');

        if($this->objParam->getParametro('id_funcionario_dependiente')!=''){
            //por defecto filtra solo superior con presupuesto
            $presupuesto = 'si';
            if($this->objParam->getParametro('presupuesto')!=''){
                $presupuesto = $this->objParam->getParametro('presupuesto');
            }

            //por defecto no filtra por gerencias
            $gerencia = 'todos';
            if($this->objParam->getParametro('gerencia')!=''){
                $gerencia = $this->objParam->getParametro('gerencia');
            }

            //verifica si existe un lista de nivel organizacionales permitidos
            $lista_blanca = 'todos';
            if($this->objParam->getParametro('lista_blanca')!=''){
                $lista_blanca = $this->objParam->getParametro('lista_blanca');
            }

            //verifica si existe un lista de nivel organizacionales NO permitidos
            $lista_negra = 'ninguno';
            if($this->objParam->getParametro('lista_negra')!=''){
                $lista_negra = $this->objParam->getParametro('lista_negra');
            }





            $this->objParam->addFiltro("FUNCAR.id_funcionario IN (select * from orga.f_get_aprobadores_x_funcionario(''" . $this->objParam->getParametro('fecha') . "'',". $this->objParam->getParametro('id_funcionario_dependiente'). ",''".$presupuesto."'',''".$gerencia."'',''".$lista_blanca."'',''".$lista_negra."'')	 AS (id_funcionario INTEGER))");
        }


        if($this->objParam->getParametro('estado_func')=='activo'){
            $this->objParam->addFiltro("(FUNCAR.estado_reg_fun = ''activo'' and current_date <= coalesce (FUNCAR.fecha_finalizacion, ''31/12/9999''::date))");
        }else if($this->objParam->getParametro('estado_func')=='inactivo'){
            $this->objParam->addFiltro("(FUNCAR.estado_reg_fun = ''inactivo'' or FUNCAR.fecha_finalizacion <= current_date)");
        }else if($this->objParam->getParametro('estado_func')=='act_desc'){
            $this->objParam->addFiltro("(
            FUNCAR.estado_reg_fun in (''activo'', ''inactivo'') or (current_date <= coalesce (FUNCAR.fecha_finalizacion, ''31/12/9999''::date) or 
            FUNCAR.fecha_finalizacion < current_date)
            )");
        }else{
            $this->objParam->addFiltro("(FUNCAR.estado_reg_fun = ''activo'' and current_date <= coalesce (FUNCAR.fecha_finalizacion, ''31/12/9999''::date))");
        }

        if($this->objParam->getParametro('fecha')!=''){

            $this->objParam->addFiltro(" ((FUNCAR.fecha_asignacion  <= ''".$this->objParam->getParametro('fecha')."'' and FUNCAR.fecha_finalizacion  >= ''".$this->objParam->getParametro('fecha')."'') or (FUNCAR.fecha_asignacion  <= ''".$this->objParam->getParametro('fecha')."'' and FUNCAR.fecha_finalizacion  is NULL))");
        }

        if( $this->objParam->getParametro('es_combo_solicitud') == 'si' ) {
            if($this->objParam->getParametro('fecha')==''){
                $date = date('d/m/Y');
            } else {
                $date = $this->objParam->getParametro('fecha');
            }
            $this->objParam->addFiltro("FUNCAR.id_funcionario IN (select *
										FROM orga.f_get_funcionarios_x_usuario_asistente(''" . $date . "'', " .
                $_SESSION["ss_id_usuario"] . ") AS (id_funcionario INTEGER)) ");
        }

        if( $this->objParam->getParametro('filter_rpc') == 'si' ) {
            $this->objParam->addFiltro("FUNCAR.id_cargo NOT in (select rpc.id_cargo from adq.trpc rpc where rpc.estado_reg = ''activo'') ");
        }


        //crea el objetoFunSeguridad que contiene todos los metodos del sistema de seguridad
        if ($this->objParam->getParametro('tipoReporte')=='excel_grid' || $this->objParam->getParametro('tipoReporte')=='pdf_grid'){
            $this->objReporte=new Reporte($this->objParam, $this);
            $this->res=$this->objReporte->generarReporteListado('MODFuncionario','listarFuncionarioCargo');
        }
        else {
            $this->objFunSeguridad=$this->create('MODFuncionario');
            //ejecuta el metodo de lista funcionarios a travez de la intefaz objetoFunSeguridad
            $this->res=$this->objFunSeguridad->listarFuncionarioCargo($this->objParam);

        }

        if($this->objParam->getParametro('todos')!=''){

            $respuesta = $this->res->getDatos();


            array_unshift ( $respuesta, array(  'id_funcionario'=>'0',
                'desc_funcionario1'=>'Todos',
                'nombre_cargo'=>'Todos',
                'email_empresa'=>'Todos',
                'carnet_discapacitado'=>'Todos') );
            $this->res->setDatos($respuesta);
        }

        //imprime respuesta en formato JSON para enviar lo a la interface (vista)
        $this->res->imprimirRespuesta($this->res->generarJson());



    }


    function guardarFuncionario(){
        //var_dump('llega control guardar');exit;
        //crea el objetoFunSeguridad que contiene todos los metodos del sistema de seguridad
        $this->objFunSeguridad=$this->create('MODFuncionario');

        //preguntamos si se debe insertar o modificar
        if($this->objParam->insertar('id_funcionario')){

            //ejecuta el metodo de insertar de la clase MODFuncionario a travez
            //de la intefaz objetoFunSeguridad
            $this->res=$this->objFunSeguridad->insertarFuncionario($this->objParam);
        }
        else{
            //ejecuta el metodo de modificar funcionario de la clase MODFuncionario a travez
            //de la intefaz objetoFunSeguridad
            $this->res=$this->objFunSeguridad->modificarFuncionario($this->objParam);
        }

        //imprime respuesta en formato JSON
        $this->res->imprimirRespuesta($this->res->generarJson());

    }

    function eliminarFuncionario(){

        //crea el objetoFunSeguridad que contiene todos los metodos del sistema de seguridad
        $this->objFunSeguridad=$this->create('MODFuncionario');
        $this->res=$this->objFunSeguridad->eliminarFuncionario($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());

    }

    function getEmailEmpresa(){
        //@id_funcionario -> funcionario del que vamos a recuperar el correo
        //crea el objetoFunSeguridad que contiene todos los metodos del sistema de seguridad
        $this->objFunSeguridad=$this->create('MODFuncionario');
        $this->res=$this->objFunSeguridad->getEmailEmpresa($this->objParam);

        //adiciona correo de notificaciones desde la onfiguracion  general

        $array = $this->res->getDatos();

        if(isset($_SESSION['_MAIL_NITIFICACIONES_1'])){
            $array['email_notificaciones_1'] = $_SESSION['_MAIL_NITIFICACIONES_1'];
        }
        if(isset($_SESSION['_MAIL_NITIFICACIONES_2'])){
            $array['email_notificaciones_2'] = $_SESSION['_MAIL_NITIFICACIONES_2'];
        }

        $this->res->setDatos($array);

        $this->res->imprimirRespuesta($this->res->generarJson());

    }

    function alertarCumpleaneroDia(){
        $this->objFunc=$this->create('MODFuncionario');
        $this->res=$this->objFunc->alertarCumpleaneroDia($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());
    }

    function listarDocumentos(){
        $this->objParam->defecto('ordenacion','tf.desc_funcionario2');
        $this->objParam->defecto('dir_ordenacion','asc');

        /*if($this->objParam->getParametro('id_uo') != '') {
            $this->objParam->addFiltro(" ger.id_uo = " . $this->objParam->getParametro('id_uo'));
        }*/

        if($this->objParam->getParametro('estado_func')=='activo'){
            $this->objParam->addFiltro("(tf.estado_reg = ''activo'' and current_date <= coalesce (uo.fecha_finalizacion, ''31/12/9999''::date))");
        }else if($this->objParam->getParametro('estado_func')=='inactivo'){
            $this->objParam->addFiltro("(tf.estado_reg = ''inactivo'' or uo.fecha_finalizacion <= current_date)");
        }else if($this->objParam->getParametro('estado_func')=='act_desc'){
            $this->objParam->addFiltro("(
            tf.estado_reg in (''activo'', ''inactivo'') or (current_date <= coalesce (uo.fecha_finalizacion, ''31/12/9999''::date) or
            uo.fecha_finalizacion < current_date)
            )");
        }else{
            $this->objParam->addFiltro("(tf.estado_reg = ''activo'' and current_date <= coalesce (uo.fecha_finalizacion, ''31/12/9999''::date))");
        }



        if($this->objParam->getParametro('tipoReporte')=='excel_grid' || $this->objParam->getParametro('tipoReporte')=='pdf_grid'){
            $this->objReporte = new Reporte($this->objParam,$this);
            $this->res = $this->objReporte->generarReporteListado('MODFuncionario','listarDocumentos');
        } else{
            $this->objFunc=$this->create('MODFuncionario');

            $this->res=$this->objFunc->listarDocumentos($this->objParam);
        }
        $this->res->imprimirRespuesta($this->res->generarJson());
    }

    //(f.e.a) reporte excel de los documentos de un funcionario
    function reporteDocumentos(){

        $this->objFunc=$this->create('MODFuncionario');
        $dataSource=$this->objFunc->reporteDocumentos();
        $this->dataSource=$dataSource->getDatos();
        $titulo_archivo = 'Reporte Documentos RRHH';
        $nombreArchivo = uniqid(md5(session_id()).$titulo_archivo).'.xls';
        $this->objParam->addParametro('nombre_archivo',$nombreArchivo);
        $this->objParam->addParametro('titulo_archivo',$titulo_archivo);
        $this->objParam->addParametro('datos',$this->dataSource);

        $this->objReporte = new RDocumentoRRHHXls($this->objParam);
        $this->objReporte->generarReporte();

        $this->mensajeExito=new Mensaje();
        $this->mensajeExito->setMensaje('EXITO','Reporte.php','Reporte generado', 'Se generó con éxito el reporte: '.$nombreArchivo,'control');
        $this->mensajeExito->setArchivoGenerado($nombreArchivo);
        $this->mensajeExito->imprimirRespuesta($this->mensajeExito->generarJson());
    }

    function urlFotoFuncionario(){
        $this->objFunc=$this->create('MODFuncionario');
        $this->res=$this->objFunc->urlFotoFuncionario($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());
    }

    function urlFotoFuncionarioByUsuario(){
        $this->objFunc=$this->create('MODFuncionario');
        $this->res=$this->objFunc->urlFotoFuncionarioByUsuario($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());
    }

    function listarAltasBajas(){

        $this->objParam->defecto('ordenacion','PERSON.nombre_completo2');
        $this->objParam->defecto('dir_ordenacion','asc');

        /*$this->objFunc=$this->create('MODFuncionario');
        $this->res=$this->objFunc->listarAltasBajas($this->objParam);*/


        if ($this->objParam->getParametro('tipoReporte')=='excel_grid' || $this->objParam->getParametro('tipoReporte')=='pdf_grid'){
            $this->objReporte=new Reporte($this->objParam, $this);
            $this->res=$this->objReporte->generarReporteListado('MODFuncionario','listarAltasBajas');
        }
        else {
            $this->objFunSeguridad=$this->create('MODFuncionario');
            //ejecuta el metodo de lista funcionarios a travez de la intefaz objetoFunSeguridad
            $this->res=$this->objFunSeguridad->listarAltasBajas($this->objParam);
        }

        $this->res->imprimirRespuesta($this->res->generarJson());
    }

    function dispararControlAsignacionCargo(){
        $this->objFunc=$this->create('MODFuncionario');
        $this->res=$this->objFunc->dispararControlAsignacionCargo($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());
    }

    //{"franklin.espinoza":"16/01/2020", "descripcion":"Obtiene el lugar de Operaciones de un funcionario"}
    function getLugarFuncionario(){

        $this->objParam->defecto('ordenacion','lug.codigo');
        $this->objParam->defecto('dir_ordenacion','asc');

        $this->objFunc=$this->create('MODFuncionario');
        $this->res=$this->objFunc->getLugarFuncionario($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());
    }

}

?>