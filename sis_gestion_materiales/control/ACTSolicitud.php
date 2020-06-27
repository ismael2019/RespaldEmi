<?php
/**
 *@package pXP
 *@file gen-ACTSolicitud.php
 *@author  (admin)
 *@date 23-12-2016 13:12:58
 *@description Clase que recibe los parametros enviados por la vista para mandar a la capa de Modelo
 */
require_once(dirname(__FILE__).'/../reportes/RRequemientoMaterielesIng.php');
require_once(dirname(__FILE__).'/../reportes/RRequemientoMaterielesMan.php');
require_once(dirname(__FILE__).'/../reportes/RRequemientoMaterielesAlm.php');
require_once(dirname(__FILE__).'/../reportes/RControlAlmacenXLS.php');
require_once(dirname(__FILE__).'/../reportes/RSalidaAlmacen.php');
require_once(dirname(__FILE__).'/../reportes/RRequerimientoMaterialesPDF.php');
require_once(dirname(__FILE__).'/../reportes/RComiteEvaluacion.php');
require_once(dirname(__FILE__).'/../reportes/RDocContratacionExtPDF.php');
require_once(dirname(__FILE__).'/../reportes/RComparacionBySPDF.php');
require_once(dirname(__FILE__).'/../reportes/RRequerimientoMaterialesCeac.php');

require_once(dirname(__FILE__).'/../reportes/RConstanciaEnvioInvitacion.php');


class ACTSolicitud extends ACTbase{

    function listarSolicitud()
    {
        $this->objParam->defecto('ordenacion', 'id_solicitud');
        $this->objParam->defecto('dir_ordenacion', 'asc');
        if($this->objParam->getParametro('id_gestion') != '' ) {
            $this->objParam->addFiltro("sol.id_gestion = " . $this->objParam->getParametro('id_gestion'));
        }
        if ($this->objParam->getParametro('tipo_interfaz') == 'RegistroSolicitud' ) {

            if ($this->objParam->getParametro('pes_estado') == 'borrador_reg') {
                $this->objParam->addFiltro("sol.estado  in (''borrador'')");
            }
            if ($this->objParam->getParametro('pes_estado') == 'vobo_area_reg') {
                $this->objParam->addFiltro("sol.estado_firma  in (''vobo_area'',''vobo_aeronavegabilidad'',''vobo_dpto_abastecimientos'')");
            }
            if ($this->objParam->getParametro('pes_estado') == 'revision_reg') {
                $this->objParam->addFiltro("sol.estado in (''cotizacion'',''compra'',''despachado'',''arribo'',''desaduanizado'',''almacen'',''cotizacion_solicitada'',''cotizacion_sin_respuesta'',''revision'') ");
            }
            if ($this->objParam->getParametro('pes_estado') == 'finalizado_reg') {
                $this->objParam->addFiltro("sol.estado  in (''finalizado'',''anulado'')");
            }
        }
        if ($this->objParam->getParametro('tipo_interfaz') == 'ConsultaRequerimientos' ) {

            if ($this->objParam->getParametro('pes_estado') == 'consulta_op') {
                $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Operaciones'') and sol.estado  in (''borrador'',''revision'',''cotizacion'',''compra'',''despachado'',''arribo'',''desaduanizado'',''almacen'',''finalizado'',''anulado'',''cotizacion_sin_respuesta'',''cotizacion_solicitada'',''comite_unidad_abastecimientos'',''comite_aeronavegabilidad'',''comite_dpto_abastecimientos'')");
            }
            if ($this->objParam->getParametro('pes_estado') == 'consulta_mal') {
                $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Mantenimiento'') and sol.estado  in (''borrador'',''revision'',''cotizacion'',''compra'',''despachado'',''arribo'',''desaduanizado'',''almacen'',''finalizado'',''anulado'',''cotizacion_sin_respuesta'',''cotizacion_solicitada'',''comite_unidad_abastecimientos'',''comite_aeronavegabilidad'',''comite_dpto_abastecimientos'')");
            }
            if ($this->objParam->getParametro('pes_estado') == 'consulta_ab') {
                $this->objParam->addFiltro("sol.origen_pedido  in (''Almacenes Consumibles o Rotables'') and sol.estado  in (''borrador'',''revision'',''cotizacion'',''compra'',''despachado'',''arribo'',''desaduanizado'',''almacen'',''finalizado'',''anulado'',''cotizacion_sin_respuesta'',''cotizacion_solicitada'',''comite_unidad_abastecimientos'',''comite_aeronavegabilidad'',''comite_dpto_abastecimientos'')");
            }
            if ($this->objParam->getParametro('pes_estado') == 'consulta_ceac') {
                $this->objParam->addFiltro("sol.origen_pedido  in (''Centro de Entrenamiento Aeronautico Civil'') and sol.estado  in (''borrador'',''revision'',''cotizacion'',''compra'',''despachado'',''arribo'',''desaduanizado'',''almacen'',''finalizado'',''anulado'',''cotizacion_sin_respuesta'',''cotizacion_solicitada'',''comite_unidad_abastecimientos'',''departamento_ceac'',''comite_dpto_abastecimientos'')");
            }
        }

        if ($this->objParam->getParametro('tipo_interfaz') == 'VistoBueno' ) {
            $this->objParam->addFiltro("sol.estado_firma  in (''vobo_area'',''vobo_aeronavegabilidad'',''vobo_dpto_abastecimientos'')");
        }
        if ($this->objParam->getParametro('tipo_interfaz') == 'SolicitudvoboComite') {
            $this->objParam->addFiltro("sol.estado  in (''comite_unidad_abastecimientos'',''comite_aeronavegabilidad'',''comite_dpto_abastecimientos'',''departamento_ceac'')");
        }
        if ($this->objParam->getParametro('tipo_interfaz') == 'SolicitudFec' ) {
            if ($this->objParam->getParametro('historico') == 'no' or  $this->objParam->getParametro('historico') == null) {

                if ($this->objParam->getParametro('pes_estado') == 'ab_origen_ing_n') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Operaciones'') and sol.estado in (''despachado'',''arribo'',''desaduanizado'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'ab_origen_man_n') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Mantenimiento'') and sol.estado  in (''despachado'',''arribo'',''desaduanizado'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'ab_origen_alm_n' ) {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Almacenes Consumibles o Rotables'') and sol.estado  in (''despachado'',''arribo'',''desaduanizado'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'ab_origen_alm_ceac' ) {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Centro de Entrenamiento Aeronautico Civil'') and sol.estado  in (''despachado'',''arribo'',''desaduanizado'')");
                }

            }else{
                if ($this->objParam->getParametro('pes_estado') == 'ab_origen_ing_n') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Operaciones'') and sol.estado  in (''arribo'',''desaduanizado'',''almacen'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'ab_origen_man_n') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Mantenimiento'') and sol.estado  in (''arribo'',''desaduanizado'',''almacen'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'ab_origen_alm_n' ) {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Almacenes Consumibles o Rotables'') and sol.estado  in (''arribo'',''desaduanizado'',''almacen'')");
                }
            }
        }
        //operaciones
        //var_dump($_SESSION["ss_id_funcionario"]);exit;
        if ($this->objParam->getParametro('tipo_interfaz') == 'PedidoOperacion' ) {
            if ($this->objParam->getParametro('historico') == 'no' or  $this->objParam->getParametro('historico') == null) {
                if ($this->objParam->getParametro('pes_estado') == 'pedido_op_pendiente') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Operaciones'') and sol.estado  in (''cotizacion'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_op_solicitada') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Operaciones'') and sol.estado  in (''cotizacion_solicitada'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_op_sin_resp') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Operaciones'') and sol.estado  in (''cotizacion_sin_respuesta'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_op_compra') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Operaciones'') and sol.estado  in (''compra'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_op_comite') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Operaciones'') and sol.estado  in (''comite_unidad_abastecimientos'',''comite_aeronavegabilidad'',''comite_dpto_abastecimientos'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_op_concluido') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Operaciones'') and sol.estado  in (''finalizado'')");
                }
            }else{
                if ($this->objParam->getParametro('pes_estado') == 'pedido_op_pendiente') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Operaciones'') and sol.estado  in (''despachado'',''arribo'',''desaduanizado'',''almacen'',''comite_unidad_abastecimientos'',''comite_aeronavegabilidad'',''comite_dpto_abastecimientos'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_op_solicitada') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Operaciones'') and sol.estado  in (''despachado'',''arribo'',''desaduanizado'',''almacen'',''comite_unidad_abastecimientos'',''comite_aeronavegabilidad'',''comite_dpto_abastecimientos'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_op_compra') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Operaciones'') and sol.estado  in (''despachado'',''arribo'',''desaduanizado'',''almacen'',''comite_unidad_abastecimientos'',''comite_aeronavegabilidad'',''comite_dpto_abastecimientos'')");
                }
            }
        }
        ///matenimiento
        if ($this->objParam->getParametro('tipo_interfaz') == 'PedidoMantenimiento' ) {
            if ($this->objParam->getParametro('historico') == 'no' or  $this->objParam->getParametro('historico') == null) {

                if ($this->objParam->getParametro('pes_estado') == 'pedido_ma_pendiente') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Mantenimiento'') and sol.estado  in (''cotizacion'')");

                }if ($this->objParam->getParametro('pes_estado') == 'pedido_ma_solicitada') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Mantenimiento'') and sol.estado  in (''cotizacion_solicitada'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_ma_sin_resp') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Mantenimiento'') and sol.estado  in (''cotizacion_sin_respuesta'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_ma_comite' ) {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Mantenimiento'') and sol.estado  in (''comite_unidad_abastecimientos'',''comite_aeronavegabilidad'',''comite_dpto_abastecimientos'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_ma_compra' ) {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Mantenimiento'') and sol.estado  in (''compra'')");
                }if ($this->objParam->getParametro('pes_estado') == 'pedido_ma_concluido') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Mantenimiento'') and sol.estado  in (''finalizado'')");
                }
            }else{
                if ($this->objParam->getParametro('pes_estado') == 'pedido_ma_pendiente') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Mantenimiento'') and sol.estado  in (''despachado'',''arribo'',''desaduanizado'',''almacen'',''comite_unidad_abastecimientos'',''comite_aeronavegabilidad'',''comite_dpto_abastecimientos'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_ma_solicitada') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Mantenimiento'') and sol.estado  in (''despachado'',''arribo'',''desaduanizado'',''almacen'',''comite_unidad_abastecimientos'',''comite_aeronavegabilidad'',''comite_dpto_abastecimientos'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_ma_compra') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Mantenimiento'') and sol.estado  in (''despachado'',''arribo'',''desaduanizado'',''almacen'',''comite_unidad_abastecimientos'',''comite_aeronavegabilidad'',''comite_dpto_abastecimientos'')");
                }
            }
        }
        //almacenes
        if ($this->objParam->getParametro('tipo_interfaz') == 'PerdidoAlmacen' ) {
            if ($this->objParam->getParametro('historico') == 'no' or  $this->objParam->getParametro('historico') == null) {
                if ($this->objParam->getParametro('pes_estado') == 'pedido_al_pendiente' ) {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Almacenes Consumibles o Rotables'') and sol.estado  in (''cotizacion'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_al_solicitada' ) {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Almacenes Consumibles o Rotables'') and sol.estado  in (''cotizacion_solicitada'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_al_sin_resp') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Almacenes Consumibles o Rotables'') and sol.estado  in (''cotizacion_sin_respuesta'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_al_comite') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Almacenes Consumibles o Rotables'') and sol.estado  in (''comite_unidad_abastecimientos'',''comite_aeronavegabilidad'',''comite_dpto_abastecimientos'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_al_compra') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Almacenes Consumibles o Rotables'') and sol.estado  in (''compra'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_al_concluido') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Almacenes Consumibles o Rotables'') and sol.estado  in (''finalizado'')");
                }

            }else{
                if ($this->objParam->getParametro('pes_estado') == 'pedido_al_pendiente') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Almacenes Consumibles o Rotables'') and sol.estado  in (''despachado'',''arribo'',''desaduanizado'',''almacen'',''comite_unidad_abastecimientos'',''comite_aeronavegabilidad'',''comite_dpto_abastecimientos'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_al_solicitada') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Almacenes Consumibles o Rotables'') and sol.estado  in (''despachado'',''arribo'',''desaduanizado'',''almacen'',''comite_unidad_abastecimientos'',''comite_aeronavegabilidad'',''comite_dpto_abastecimientos'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_al_compra') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Almacenes Consumibles o Rotables'') and sol.estado  in (''despachado'',''arribo'',''desaduanizado'',''almacen'',''comite_unidad_abastecimientos'',''comite_aeronavegabilidad'',''comite_dpto_abastecimientos'')");
                }
            }
        }
        //dgac
        if ($this->objParam->getParametro('tipo_interfaz') == 'PedidoDgac' ) {
            if ($this->objParam->getParametro('historico') == 'no' or  $this->objParam->getParametro('historico') == null) {
                if ($this->objParam->getParametro('pes_estado') == 'pedido_dgac_pendiente' ) {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Centro de Entrenamiento Aeronautico Civil'') and sol.estado  in (''cotizacion'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_dgac_solicitada' ) {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Centro de Entrenamiento Aeronautico Civil'') and sol.estado  in (''cotizacion_solicitada'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_dgac_sin_resp') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Centro de Entrenamiento Aeronautico Civil'') and sol.estado  in (''cotizacion_sin_respuesta'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_dgac_comite') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Centro de Entrenamiento Aeronautico Civil'') and sol.estado  in (''comite_unidad_abastecimientos'',''departamento_ceac'',''comite_dpto_abastecimientos'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_dgac_compra') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Centro de Entrenamiento Aeronautico Civil'') and sol.estado  in (''compra'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_dgac_concluido') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Centro de Entrenamiento Aeronautico Civil'') and sol.estado  in (''finalizado'')");
                }

            }else{
                if ($this->objParam->getParametro('pes_estado') == 'pedido_dgac_pendiente') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Centro de Entrenamiento Aeronautico Civil'') and sol.estado  in (''despachado'',''arribo'',''desaduanizado'',''almacen'',''comite_unidad_abastecimientos'',''comite_aeronavegabilidad'',''comite_dpto_abastecimientos'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_dgac_solicitada') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Centro de Entrenamiento Aeronautico Civil'') and sol.estado  in (''despachado'',''arribo'',''desaduanizado'',''almacen'',''comite_unidad_abastecimientos'',''comite_aeronavegabilidad'',''comite_dpto_abastecimientos'')");
                }
                if ($this->objParam->getParametro('pes_estado') == 'pedido_dgac_compra') {
                    $this->objParam->addFiltro("sol.origen_pedido  in (''Centro de Entrenamiento Aeronautico Civil'') and sol.estado  in (''despachado'',''arribo'',''desaduanizado'',''almacen'',''comite_unidad_abastecimientos'',''comite_aeronavegabilidad'',''comite_dpto_abastecimientos'')");
                }
            }
        }
        if ($this->objParam->getParametro('tipo_interfaz') == 'almacen' ) {
            if ($this->objParam->getParametro('pes_estado') == 'almacen') {
                $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Operaciones'') and  sol.estado  in (''almacen'')");
            }
            if ($this->objParam->getParametro('pes_estado') == 'origen_al_man') {
                $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Mantenimiento'') and  sol.estado in (''almacen'')");
            }
            if ($this->objParam->getParametro('pes_estado') == 'origen_al_ab') {
                $this->objParam->addFiltro("sol.origen_pedido  in (''Almacenes Consumibles o Rotables'') and  sol.estado  in (''almacen'')");
            }
        }
        if ($this->objParam->getParametro('tipo_interfaz') == 'SolArchivado' ) {
            if ($this->objParam->getParametro('pes_estado') == 'archivado_ing') {
                $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Operaciones'') and  sol.estado  in (''finalizado'')");
            }
            if ($this->objParam->getParametro('pes_estado') == 'archivado_man') {
                $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Mantenimiento'') and  sol.estado in (''finalizado'')");
            }
            if ($this->objParam->getParametro('pes_estado') == 'archivado_alm') {
                $this->objParam->addFiltro("sol.origen_pedido  in (''Almacenes Consumibles o Rotables'') and  sol.estado  in (''finalizado'')");
            }
            if ($this->objParam->getParametro('pes_estado') == 'archivado_ceac') {
                $this->objParam->addFiltro("sol.origen_pedido  in (''Centro de Entrenamiento Aeronautico Civil'') and  sol.estado  in (''finalizado'')");
            }
        }
        if ($this->objParam->getParametro('tipo_interfaz') == 'ProcesoCompra' ) {
            if ($this->objParam->getParametro('pes_estado') == 'origen_ing') {
                $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Operaciones'') and  sol.estado  in (''revision'')");
            }
            if ($this->objParam->getParametro('pes_estado') == 'origen_man') {
                $this->objParam->addFiltro("sol.origen_pedido  in (''Gerencia de Mantenimiento'') and  sol.estado in (''revision'')");
            }
            if ($this->objParam->getParametro('pes_estado') == 'origen_alm') {
                $this->objParam->addFiltro("sol.origen_pedido  in (''Almacenes Consumibles o Rotables'') and  sol.estado  in (''revision'')");
            }
            if ($this->objParam->getParametro('pes_estado') == 'origen_dgac') {
                $this->objParam->addFiltro("sol.origen_pedido  in (''Centro de Entrenamiento Aeronautico Civil'') and  sol.estado  in (''revision'')");
            }
        }
        if($this->objParam->getParametro('tipoReporte')=='excel_grid' || $this->objParam->getParametro('tipoReporte')=='pdf_grid'){
            $this->objReporte = new Reporte($this->objParam,$this);
            $this->res = $this->objReporte->generarReporteListado('MODSolicitud','listarSolicitud');
        } else{
            $this->objFunc=$this->create('MODSolicitud');

            $this->res=$this->objFunc->listarSolicitud($this->objParam);
        }
        $this->res->imprimirRespuesta($this->res->generarJson());
    }

    function insertarSolicitud(){
        $this->objFunc=$this->create('MODSolicitud');
        if($this->objParam->insertar('id_solicitud')){
            $this->res=$this->objFunc->insertarSolicitud($this->objParam);
        } else{
            $this->res=$this->objFunc->modificarSolicitud($this->objParam);
        }
        $this->res->imprimirRespuesta($this->res->generarJson());
    }
    function insertarSolicitudCompleta(){
        $this->objFunc=$this->create('MODSolicitud');
        if($this->objParam->insertar('id_solicitud')){
            $this->res=$this->objFunc->insertarSolicitudCompleta($this->objParam);
            //var_dump($this->res); exit;
        }
        $this->res->imprimirRespuesta($this->res->generarJson());
    }

    function eliminarSolicitud(){
        $this->objFunc=$this->create('MODSolicitud');
        $this->res=$this->objFunc->eliminarSolicitud($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());
    }
    function listarMatricula(){
        $this->objParam->defecto('ordenacion','id_orden_trabajo');
        $this->objParam->defecto('dir_ordenacion','asc');
        if($this->objParam->getParametro('id_orden_trabajo') != '') {
            $this->objParam->addFiltro(" ord.id_orden_trabajo = " . $this->objParam->getParametro('id_orden_trabajo'));
        }

        $this->objFunc=$this->create('MODSolicitud');
        $this->res=$this->objFunc->listarMatricula($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());
    }

    function listarFuncionarioRegistro(){
        $this->objParam->defecto('ordenacion','id_funcionario');
        $this->objParam->defecto('dir_ordenacion','asc');
        if($this->objParam->getParametro('id_funcionario') != '') {
            $this->objParam->addFiltro(" f.id_funcionario = " . $this->objParam->getParametro('id_orden_trabajo'));
        }

        $this->objFunc=$this->create('MODSolicitud');
        $this->res=$this->objFunc->listarFuncionarios($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());
    }
    function getDatos(){
        $this->objParam->defecto('ordenacion','id_funcionario');
        $this->objParam->defecto('dir_ordenacion','asc');


        $this->objFunc=$this->create('MODSolicitud');
        $this->res=$this->objFunc->listaGetDatos($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());
    }

    function siguienteEstadoSolicitud(){
        $this->objFunc=$this->create('MODSolicitud');

        $this->objParam->addParametro('id_funcionario_usu',$_SESSION["id_usuario_reg"]);

        $this->res=$this->objFunc->siguienteEstadoSolicitud($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());
    }
    function anteriorEstadoSolicitud(){
        $this->objFunc=$this->create('MODSolicitud');
        $this->objParam->addParametro('id_funcionario_usu',$_SESSION["ss_id_funcionario"]);
        $this->res=$this->objFunc->anteriorEstadoSolicitud($this->objParam);

        $this->res->imprimirRespuesta($this->res->generarJson());
    }
    function inicioEstadoSolicitud(){
        $this->objFunc=$this->create('MODSolicitud');
        $this->objParam->addParametro('id_funcionario_usu',$_SESSION["ss_id_funcionario"]);
        $this->res=$this->objFunc->inicioEstadoSolicitud($this->objParam);

        $this->res->imprimirRespuesta($this->res->generarJson());
    }
    function reporteRequerimientoIng (){
        $this->objFunc=$this->create('MODSolicitud');
        $this->res=$this->objFunc->listarRequerimiento($this->objParam);

        $this->objFunc=$this->create('MODSolicitud');
        $this->res2=$this->objFunc->listasFrimas($this->objParam);
        $this->objFunc=$this->create('MODSolicitud');
        $this->res3=$this->objFunc->listasFrimas2($this->objParam);

        //var_dump($this->res2);exit;
        //obtener titulo del reporte
        $titulo = 'Requerimiento de Materiales';
        //Genera el nombre del archivo (aleatorio + titulo)
        $nombreArchivo=uniqid(md5(session_id()).$titulo);
        $nombreArchivo.='.pdf';
        $this->objParam->addParametro('orientacion','P');
        $this->objParam->addParametro('tamano','LETTER');
        $this->objParam->addParametro('nombre_archivo',$nombreArchivo);
        //Instancia la clase de pdf

        $this->objReporteFormato=new RRequemientoMaterielesIng($this->objParam);
        $this->objReporteFormato->setDatos($this->res->datos, $this->res2->datos, $this->res3->datos);
        $this->objReporteFormato->generarReporte();
        $this->objReporteFormato->output($this->objReporteFormato->url_archivo,'F');


        $this->mensajeExito=new Mensaje();
        $this->mensajeExito->setMensaje('EXITO','Reporte.php','Reporte generado',
            'Se generó con éxito el reporte: '.$nombreArchivo,'control');
        $this->mensajeExito->setArchivoGenerado($nombreArchivo);
        $this->mensajeExito->imprimirRespuesta($this->mensajeExito->generarJson());
    }

    function reporteRequerimientoMan (){
        $this->objFunc=$this->create('MODSolicitud');
        $this->res=$this->objFunc->listarRequerimiento($this->objParam);
        $this->objFunc=$this->create('MODSolicitud');
        $this->res2=$this->objFunc->listasFrimas($this->objParam);
        $this->objFunc=$this->create('MODSolicitud');
        $this->res3=$this->objFunc->listasFrimas2($this->objParam);
        //obtener titulo del reporte
        $titulo = 'Requerimiento de Materiales';
        //Genera el nombre del archivo (aleatorio + titulo)
        $nombreArchivo=uniqid(md5(session_id()).$titulo);
        $nombreArchivo.='.pdf';
        $this->objParam->addParametro('orientacion','P');
        $this->objParam->addParametro('tamano','LETTER');
        $this->objParam->addParametro('nombre_archivo',$nombreArchivo);
        //Instancia la clase de pdf

        $this->objReporteFormato=new RRequemientoMaterielesMan($this->objParam);
        $this->objReporteFormato->setDatos($this->res->datos, $this->res2->datos,$this->res3->datos );
        $this->objReporteFormato->generarReporte();
        $this->objReporteFormato->output($this->objReporteFormato->url_archivo,'F');


        $this->mensajeExito=new Mensaje();
        $this->mensajeExito->setMensaje('EXITO','Reporte.php','Reporte generado',
            'Se generó con éxito el reporte: '.$nombreArchivo,'control');
        $this->mensajeExito->setArchivoGenerado($nombreArchivo);
        $this->mensajeExito->imprimirRespuesta($this->mensajeExito->generarJson());
    }
    function reporteRequerimientoCeac (){
        $this->objFunc=$this->create('MODSolicitud');
        $this->res=$this->objFunc->listarRequerimiento($this->objParam);
        $this->objFunc=$this->create('MODSolicitud');
        $this->res2=$this->objFunc->listasFrimas($this->objParam);
        $this->objFunc=$this->create('MODSolicitud');
        $this->res3=$this->objFunc->listasFrimas2($this->objParam);
        //obtener titulo del reporte
        $titulo = 'Requerimiento de Materiales';
        //Genera el nombre del archivo (aleatorio + titulo)
        $nombreArchivo=uniqid(md5(session_id()).$titulo);
        $nombreArchivo.='.pdf';
        $this->objParam->addParametro('orientacion','P');
        $this->objParam->addParametro('tamano','LETTER');
        $this->objParam->addParametro('nombre_archivo',$nombreArchivo);
        //Instancia la clase de pdf

        $this->objReporteFormato=new RRequerimientoMaterialesCeac($this->objParam);
        $this->objReporteFormato->setDatos($this->res->datos, $this->res2->datos,$this->res3->datos );
        $this->objReporteFormato->generarReporte();
        $this->objReporteFormato->output($this->objReporteFormato->url_archivo,'F');


        $this->mensajeExito=new Mensaje();
        $this->mensajeExito->setMensaje('EXITO','Reporte.php','Reporte generado',
            'Se generó con éxito el reporte: '.$nombreArchivo,'control');
        $this->mensajeExito->setArchivoGenerado($nombreArchivo);
        $this->mensajeExito->imprimirRespuesta($this->mensajeExito->generarJson());
    }

    function reporteRequerimientoAlm (){
        $this->objFunc=$this->create('MODSolicitud');
        $this->res=$this->objFunc->listarRequerimiento($this->objParam);
        $this->objFunc=$this->create('MODSolicitud');
        $this->res2=$this->objFunc->listasFrimas($this->objParam);


        //obtener titulo del reporte
        $titulo = 'Requerimiento de Materiales';
        //Genera el nombre del archivo (aleatorio + titulo)
        $nombreArchivo=uniqid(md5(session_id()).$titulo);
        $nombreArchivo.='.pdf';
        $this->objParam->addParametro('orientacion','L');
        $this->objParam->addParametro('tamano','LETTER');
        $this->objParam->addParametro('nombre_archivo',$nombreArchivo);
        //Instancia la clase de pdf

        $this->objReporteFormato=new RRequemientoMaterielesAlm($this->objParam);
        $this->objReporteFormato->setDatos($this->res->datos, $this->res2->datos);
        $this->objReporteFormato->generarReporte();
        $this->objReporteFormato->output($this->objReporteFormato->url_archivo,'F');


        $this->mensajeExito=new Mensaje();
        $this->mensajeExito->setMensaje('EXITO','Reporte.php','Reporte generado',
            'Se generó con éxito el reporte: '.$nombreArchivo,'control');
        $this->mensajeExito->setArchivoGenerado($nombreArchivo);
        $this->mensajeExito->imprimirRespuesta($this->mensajeExito->generarJson());
    }
    function compararNroJustificacion(){

        $this->objFunc=$this->create('MODSolicitud');
        $this->res=$this->objFunc->listaNroJustificacion($this->objParam);
        //var_dump($this->res); exit;
        $this->res->imprimirRespuesta($this->res->generarJson());
    }
    function ControlPartesAlmacen (){

        $this->objFunc=$this->create('MODSolicitud');
        $this->res=$this->objFunc->ControlPartesAlmacen ($this->objParam);
        //obtener titulo de reporte
        $titulo ='Control Almacen';
        //Genera el nombre del archivo (aleatorio + titulo)
        $nombreArchivo=uniqid(md5(session_id()).$titulo);

        $nombreArchivo.='.xls';
        $this->objParam->addParametro('nombre_archivo',$nombreArchivo);
        $this->objParam->addParametro('datos',$this->res->datos);
        //Instancia la clase de excel
        $this->objReporteFormato=new RControlAlmacenXLS($this->objParam);
        $this->objReporteFormato->generarDatos();
        $this->objReporteFormato->generarReporte();

        $this->mensajeExito=new Mensaje();
        $this->mensajeExito->setMensaje('EXITO','Reporte.php','Reporte generado',
            'Se generó con éxito el reporte: '.$nombreArchivo,'control');
        $this->mensajeExito->setArchivoGenerado($nombreArchivo);
        $this->mensajeExito->imprimirRespuesta($this->mensajeExito->generarJson());

    }
    /* function cambiarRevision(){
         $this->objFunc=$this->create('MODSolicitud');
         $this->res=$this->objFunc->listarRevision($this->objParam);
         $this->res->imprimirRespuesta($this->res->generarJson());

     }*/
    function reporteSalidaAlmacen (){
        $this->objFunc=$this->create('MODSolicitud');
        $this->res=$this->objFunc->listarRequerimiento($this->objParam);
        $this->objFunc=$this->create('MODSolicitud');
        $this->res2=$this->objFunc->listasFrimas($this->objParam);

        //obtener titulo del reporte
        $titulo = 'Requerimiento de Materiales';
        //Genera el nombre del archivo (aleatorio + titulo)
        $nombreArchivo=uniqid(md5(session_id()).$titulo);
        $nombreArchivo.='.pdf';
        $this->objParam->addParametro('orientacion','L');
        $this->objParam->addParametro('tamano','A4');
        $this->objParam->addParametro('nombre_archivo',$nombreArchivo);
        //Instancia la clase de pdf

        $this->objReporteFormato=new RSalidaAlmacen($this->objParam);
        $this->objReporteFormato->setDatos($this->res->datos, $this->res2->datos );
        $this->objReporteFormato->generarReporte();
        $this->objReporteFormato->output($this->objReporteFormato->url_archivo,'F');


        $this->mensajeExito=new Mensaje();
        $this->mensajeExito->setMensaje('EXITO','Reporte.php','Reporte generado',
            'Se generó con éxito el reporte: '.$nombreArchivo,'control');
        $this->mensajeExito->setArchivoGenerado($nombreArchivo);
        $this->mensajeExito->imprimirRespuesta($this->mensajeExito->generarJson());
    }
    function listarEstado(){
        $this->objParam->defecto('ordenacion','id_tipo_estado');
        $this->objParam->defecto('dir_ordenacion','asc');
        if($this->objParam->getParametro('id_tipo_estado') != '') {
            $this->objParam->addFiltro(" t.id_tipo_estado = " . $this->objParam->getParametro('id_tipo_estado'));
        }
        $this->objFunc=$this->create('MODSolicitud');
        $this->res=$this->objFunc->listarEstado($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());
    }
    function listarEstadoOp(){
        $this->objParam->defecto('ordenacion','id_tipo_estado');
        $this->objParam->defecto('dir_ordenacion','asc');
        if($this->objParam->getParametro('id_tipo_estado') != '') {
            $this->objParam->addFiltro(" t.id_tipo_estado = " . $this->objParam->getParametro('id_tipo_estado'));
        }
        $this->objFunc=$this->create('MODSolicitud');
        $this->res=$this->objFunc->listarEstadoOp($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());
    }
    function listarEstadoRo(){
        $this->objParam->defecto('ordenacion','id_tipo_estado');
        $this->objParam->defecto('dir_ordenacion','asc');
        if($this->objParam->getParametro('id_tipo_estado') != '') {
            $this->objParam->addFiltro(" t.id_tipo_estado = " . $this->objParam->getParametro('id_tipo_estado'));
        }
        $this->objFunc=$this->create('MODSolicitud');
        $this->res=$this->objFunc->listarEstadoRo($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());
    }

    function listarEstadoSAC(){
        $this->objParam->defecto('ordenacion','id_tipo_estado');
        $this->objParam->defecto('dir_ordenacion','asc');
        if($this->objParam->getParametro('id_tipo_estado') != '') {
            $this->objParam->addFiltro(" t.id_tipo_estado = " . $this->objParam->getParametro('id_tipo_estado'));
        }
        $this->objFunc=$this->create('MODSolicitud');
        $this->res=$this->objFunc->listarEstadoSAC($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());
    }
    function reporteRequerimientoMateriales (){
        $this->objFunc=$this->create('MODSolicitud');
        $this->res=$this->objFunc->listarRequerimiento($this->objParam);
        $this->objFunc=$this->create('MODSolicitud');
        $this->res2=$this->objFunc->listasFrimas($this->objParam);

        //obtener titulo del reporte
        $titulo = 'Requerimiento de Materiales';
        //Genera el nombre del archivo (aleatorio + titulo)
        $nombreArchivo=uniqid(md5(session_id()).$titulo);
        $nombreArchivo.='.pdf';
        $this->objParam->addParametro('orientacion','P');
        $this->objParam->addParametro('tamano','LETTER');
        $this->objParam->addParametro('nombre_archivo',$nombreArchivo);
        //Instancia la clase de pdf

        $this->objReporteFormato=new RRequerimientoMaterialesPDF($this->objParam);
        $this->objReporteFormato->setDatos($this->res->datos, $this->res2->datos );
        $this->objReporteFormato->generarReporte();
        $this->objReporteFormato->output($this->objReporteFormato->url_archivo,'F');


        $this->mensajeExito=new Mensaje();
        $this->mensajeExito->setMensaje('EXITO','Reporte.php','Reporte generado',
            'Se generó con éxito el reporte: '.$nombreArchivo,'control');
        $this->mensajeExito->setArchivoGenerado($nombreArchivo);
        $this->mensajeExito->imprimirRespuesta($this->mensajeExito->generarJson());
    }
    function iniciarDisparo(){

        $this->objFunc=$this->create('MODSolicitud');
        $this->res=$this->objFunc->iniciarDisparo($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());

    }
    function siguienteDisparo(){
        $this->objFunc=$this->create('MODSolicitud');

        $this->objParam->addParametro('id_funcionario_usu',$_SESSION["id_usuario_reg"]);

        $this->res=$this->objFunc->siguienteDisparo($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());
    }
    function anteriorDisparo(){
        $this->objFunc=$this->create('MODSolicitud');
        $this->objParam->addParametro('id_funcionario_usu',$_SESSION["ss_id_funcionario"]);
        $this->res=$this->objFunc->anteriorEstadoDisparo($this->objParam);

        $this->res->imprimirRespuesta($this->res->generarJson());
    }
    function inicioEstadoSolicitudDisparo(){
        $this->objFunc=$this->create('MODSolicitud');
        $this->objParam->addParametro('id_funcionario_usu',$_SESSION["ss_id_funcionario"]);
        $this->res=$this->objFunc->inicioEstadoSolicitudDisparo($this->objParam);

        $this->res->imprimirRespuesta($this->res->generarJson());
    }

    function reporteDocContratacionExt(){
        $this->objFunc=$this->create('MODSolicitud');
        $dataSource=$this->objFunc->reporteDocContratacionExt();
        $this->dataSource=$dataSource->getDatos();

        $nombreArchivo = uniqid(md5(session_id()).'[Reporte-DocContratacionExt]').'.pdf';
        $this->objParam->addParametro('orientacion','P');
        $this->objParam->addParametro('tamano','LETTER');
        $this->objParam->addParametro('nombre_archivo',$nombreArchivo);

        $this->objReporte = new RDocContratacionExtPDF($this->objParam);
        $this->objReporte->setDatos($this->dataSource);
        $this->objReporte->generarReporte();
        $this->objReporte->output($this->objReporte->url_archivo,'F');

        $this->mensajeExito = new Mensaje();
        $this->mensajeExito->setMensaje('EXITO', 'Reporte.php', 'Reporte generado', 'Se generó con éxito el reporte: ' . $nombreArchivo, 'control');
        $this->mensajeExito->setArchivoGenerado($nombreArchivo);
        $this->mensajeExito->imprimirRespuesta($this->mensajeExito->generarJson());


    }
    /*function reporteDocContratacionExt( $create_file = false){
        $dataSource = new DataSource();

        $this->objFunc=$this->create('MODSolicitud');
        $resultadoSolicitud = $this->objFunc->reporteDocContratacionExt();
        $datoSolicitud = $resultadoSolicitud->getDatos();
        //var_dump($datoSolicitud);exit;
        $dataSource->putParameter('solicitud', $datoSolicitud);

        $nombreArchivo = uniqid(md5(session_id()).'[Reporte-DocContratacionExt]') . '.pdf';
        $this->objParam->addParametro('orientacion','P');
        $this->objParam->addParametro('tamano','LETTER');
        $this->objParam->addParametro('titulo_archivo','SOLICITUD DE COTIZACION');
        $this->objParam->addParametro('nombre_archivo',$nombreArchivo);
        //build the report
        $reporte = new RDocContratacionExtPDF($this->objParam);
        $reporte->setDataSource($dataSource);
        $datos= $reporte->generarReporte();
        if(!$create_file){
            $mensajeExito = new Mensaje();
            $mensajeExito->setMensaje('EXITO','Reporte.php','Reporte generado','Se generó con éxito el reporte: '.$nombreArchivo,'control');
            $mensajeExito->setArchivoGenerado($nombreArchivo);
            $mensajeExito->setDatos($datos);
            $this->res = $mensajeExito;
            $this->res->imprimirRespuesta($this->res->generarJson());
        }else{

            return dirname(__FILE__).'/../../reportes_generados/'.$nombreArchivo;
        }

    }*/

    function listarComiteEvaluacion (){
        $this->objFunc=$this->create('MODSolicitud');
        $this->res=$this->objFunc->listarComiteEvaluacion($this->objParam);

        //obtener titulo del reporte
        $titulo = 'Requerimiento de Materiales';
        //Genera el nombre del archivo (aleatorio + titulo)
        $nombreArchivo=uniqid(md5(session_id()).$titulo);
        $nombreArchivo.='.pdf';
        $this->objParam->addParametro('orientacion','P');
        $this->objParam->addParametro('tamano','LETTER');
        $this->objParam->addParametro('nombre_archivo',$nombreArchivo);
        //Instancia la clase de pdf

        $this->objReporteFormato=new RComiteEvaluacion($this->objParam);
        $this->objReporteFormato->setDatos($this->res->datos);
        $this->objReporteFormato->generarReporte();
        $this->objReporteFormato->output($this->objReporteFormato->url_archivo,'F');


        $this->mensajeExito=new Mensaje();
        $this->mensajeExito->setMensaje('EXITO','Reporte.php','Reporte generado',
            'Se generó con éxito el reporte: '.$nombreArchivo,'control');
        $this->mensajeExito->setArchivoGenerado($nombreArchivo);
        $this->mensajeExito->imprimirRespuesta($this->mensajeExito->generarJson());
    }

    function listarProveedor(){

        $this->objParam->defecto('ordenacion','id_proveedor');
        $this->objParam->defecto('dir_ordenacion','asc');

        if ($this->objParam->getParametro('tipo') != '') {
            $this->objParam->addFiltro("provee.tipo  = ''". $this->objParam->getParametro('tipo')."''");
        }
        $this->objFunc=$this->create('MODSolicitud');
        $this->res=$this->objFunc->listarProveedor();

        if($this->objParam->getParametro('_adicionar')!=''){

            $respuesta = $this->res->getDatos();
            array_unshift ( $respuesta, array('rotulo_comercial'=>'Todos','desc_proveedor'=>'Todos','id_proveedor'=>'0','email'=>'todo@gmail.com'));
            $this->res->setDatos($respuesta);
        }
        $this->res->imprimirRespuesta($this->res->generarJson());
    }
    //fea 04/07/2017
    function setCorreosCotizacion(){
        $this->objFunc=$this->create('MODSolicitud');
        $this->res=$this->objFunc->setCorreosCotizacion();
        $this->res->imprimirRespuesta($this->res->generarJson());
    }

    //fea 05/07/2017
    function reporteComparacionByS(){
        $this->objFunc=$this->create('MODSolicitud');
        $dataSource=$this->objFunc->reporteComparacionByS();
        $this->dataSource=$dataSource->getDatos();

        $nombreArchivo = uniqid(md5(session_id()).'[Reporte-ComparacionBienesyServicios]').'.pdf';
        $this->objParam->addParametro('orientacion','P');
        $this->objParam->addParametro('tamano','LETTER');
        $this->objParam->addParametro('nombre_archivo',$nombreArchivo);

        $this->objReporte = new RComparacionBySPDF($this->objParam);
        $this->objReporte->setDatos($this->dataSource);
        $this->objReporte->generarReporte();
        $this->objReporte->output($this->objReporte->url_archivo,'F');


        $this->mensajeExito=new Mensaje();
        $this->mensajeExito->setMensaje('EXITO','Reporte.php','Reporte generado', 'Se generó con éxito el reporte: '.$nombreArchivo,'control');
        $this->mensajeExito->setArchivoGenerado($nombreArchivo);
        $this->mensajeExito->imprimirRespuesta($this->mensajeExito->generarJson());
    }
    //fea 06/07/2017
    function verificarCorreosProveedor(){
        $this->objFunc=$this->create('MODSolicitud');
        $this->res=$this->objFunc->verificarCorreosProveedor();

        $this->res->imprimirRespuesta($this->res->generarJson());
    }
    function clonarSolicitud(){
        $this->objFunc=$this->create('MODSolicitud');
        $this->res=$this->objFunc->clonarSolicitud();

        $this->res->imprimirRespuesta($this->res->generarJson());
    }
    function generarPAC(){
        $this->objFunc=$this->create('MODSolicitud');
        $this->res=$this->objFunc->generarPAC($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());
    }

    function ReporteConstanciaEnvioInvitacion(){

        $this->objFunc=$this->create('MODSolicitud');
        $this->res=$this->objFunc->ReporteConstanciaEnvioInvitacion($this->objParam);
//        $dataSource=$this->objFunc->ReporteConstanciaEnvioInvitacion();
//        $this->dataSource=$dataSource->getDatos();

        //obtener titulo del reporte
        $titulo = 'Correo';
        //Genera el nombre del archivo (aleatorio + titulo)
        $nombreArchivo=uniqid(md5(session_id()).$titulo);
        $nombreArchivo.='.pdf';
        $this->objParam->addParametro('orientacion','P');
        $this->objParam->addParametro('nombre_archivo',$nombreArchivo);

        $this->objReporteFormato=new RConstanciaEnvioInvitacion($this->objParam);
        $this->objReporteFormato->setDatos($this->res->datos);
        $this->objReporteFormato->generarReporte();
        $this->objReporteFormato->output($this->objReporteFormato->url_archivo,'F');
        $this->mensajeExito=new Mensaje();
        $this->mensajeExito->setMensaje('EXITO','Reporte.php','Reporte generado',
            'Se generó con éxito el reporte: '.$nombreArchivo,'control');
        $this->mensajeExito->setArchivoGenerado($nombreArchivo);
        $this->mensajeExito->imprimirRespuesta($this->mensajeExito->generarJson());
//var_dump($this->res);
    }





}

?>