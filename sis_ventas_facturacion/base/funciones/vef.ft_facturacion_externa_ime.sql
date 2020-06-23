CREATE OR REPLACE FUNCTION vef.ft_facturacion_externa_ime (
  p_administrador integer,
  p_id_usuario integer,
  p_tabla varchar,
  p_transaccion varchar
)
RETURNS varchar AS
$body$
/**************************************************************************
 SISTEMA:		Sistema de Ventas
 FUNCION: 		vef.ft_ft_facturacion_externa_ime
 DESCRIPCION:   Funcion para ir registrando los datos en la tabla de ventas y ventas detalle
 AUTOR: 		Ismael Valdivia
 FECHA:	        23-05-2020
 COMENTARIOS:
***************************************************************************
 HISTORIAL DE MODIFICACIONES:

 DESCRIPCION:
 AUTOR:
 FECHA:
***************************************************************************/

DECLARE

	v_nro_requerimiento    	integer;
	v_parametros           	record;
	v_id_requerimiento     	integer;
	v_resp		            varchar;
	v_nombre_funcion        text;
	v_mensaje_error         text;
	v_id_prioridad	integer;

    /*Aumentando estas variables*/
    v_num_tramite			varchar;
    v_id_proceso_wf			integer;
    v_id_estado_wf			integer;
    v_codigo_estado			varchar;
    v_id_gestion			integer;
    v_id_venta				integer;
    v_codigo_proceso		varchar;
    v_existencia_cliente	integer;
    v_id_cliente			integer;
    v_hora_estimada			time;
    v_id_sucursal			integer;
    v_id_dosificacion		integer;
    v_id_punto_venta		integer;
    v_codigo_tabla			varchar;
    v_num_ven				varchar;
	v_id_venta_detalle		integer;
    v_total_venta			numeric;
    v_registros				record;
    v_id_periodo			integer;
    v_id_tipo_estado_sig	integer;
    v_id_estado_wf_sig		integer;
    v_id_funcionario_sig	integer;
    v_venta					record;
    v_estado_finalizado		integer;
    v_tabla					varchar;
    v_id_tipo_estado		integer;
    v_codigo_estado_siguiente varchar;
    v_es_fin				varchar;
    v_acceso_directo 		varchar;
    v_clase 				varchar;
    v_parametros_ad 		varchar;
    v_tipo_noti 			varchar;
    v_titulo 				varchar;
    v_id_depto				integer;
     v_obs					text;
     v_id_estado_actual		integer;
     v_fecha_venta			date;
    v_id_actividad_economica	integer[];
    v_dosificacion			record;
    v_nro_factura			integer;
    v_id_entidad			integer;
    v_mensaje_error_punto_venta	varchar;
    v_mensaje_general		varchar;
    v_tipo_cambio			numeric;
    v_id_moneda				integer;
    v_mensaje_error_moneda	varchar;
    v_respaldo				record;
    v_tipo_usuario			varchar;
    v_res					varchar;
    v_existe_tc				integer;
    v_exento				numeric;
    v_precio_unitario		numeric;
    v_precio_unitario_convertido numeric;
    v_mensaje_correo		varchar;
    v_parametros_correo		varchar;
    v_documento_adjunto		varchar;
    v_correos				varchar;
BEGIN

    v_nombre_funcion = 'vef.ft_facturacion_externa_ime';
    v_parametros = pxp.f_get_record(p_tabla);

	/*********************************
 	#TRANSACCION:  'VEF_INS_FAC_EXT_IME'
 	#DESCRIPCION:	Insercion de registros
    #AUTOR: 		Ismael Valdivia
    #FECHA:	        23-05-2020
	***********************************/

	if(p_transaccion='VEF_INS_FAC_EXT_IME')then

        begin

        /*Aqui pondremos un control para que se vaya registrando los clientes a los que se vende*/
          select count (cli.id_cliente) into v_existencia_cliente
          from vef.tcliente cli
          where cli.nit = replace(v_parametros.nit_cliente,' ','');

          if (v_existencia_cliente = 0) then
          		INSERT INTO
                  vef.tcliente
                  (
                    id_usuario_reg,
                    fecha_reg,
                    estado_reg,
                    nombre_factura,
                    nit
                  )
                VALUES (
                  p_id_usuario,
                  now(),
                  'activo',
                  upper(replace(v_parametros.razon_social,' ','')),
                  replace(v_parametros.nit_cliente,' ','')
                ) returning id_cliente into v_id_cliente;
          else
          		select cli.id_cliente into v_id_cliente
                from vef.tcliente cli
                where cli.nit = replace(v_parametros.nit_cliente,' ','');
          end if;

        /****************************************************************************************/

        /*Obtenemos la hora de emision*/
        select to_char(current_timestamp, 'HH12:MI:SS') into v_hora_estimada;
        /******************************/

        	  /*Aqui obtenemos el tipo de cambio con sus decimales*/
              v_tipo_cambio = (v_parametros.tipo_cambio/100);
              /****************************************************/

              select mon.id_moneda into v_id_moneda
              from param.tmoneda mon
              where mon.codigo_internacional = v_parametros.moneda;

              /*Aqui hacemos la conversion si los precios del servicio estan en dolares*/
              if (v_id_moneda = 2) then
              	  v_exento = (v_parametros.exento*v_tipo_cambio);
              else
                  v_exento = v_parametros.exento;
              end if;



        	  if (v_id_moneda is null) then
              	raise exception 'El codigo de moneda: % no existe favor consulte con el area de sistemas.',v_parametros.moneda;
             else

             /*Recuperamos el tipo de Cambio para hacer la inserccion*/
             /*Verificamos que el tipo de cambio no exista para la fecha actual*/
             select count(*) into v_existe_tc
             from param.ttipo_cambio cam
             where cam.fecha = now()::date and cam.id_moneda = 2;
             /*Si no se tiene registro del tipo de cambio entonces insertamos*/
             IF (v_existe_tc = 0) then
             insert into param.ttipo_cambio(
              estado_reg,
              fecha,
              observaciones,
              compra,
              venta,
              oficial,
              id_moneda,
              fecha_reg,
              id_usuario_reg,
              fecha_mod,
              id_usuario_mod
              ) values(
              'activo',
              now()::date,
              'Registro tipo de cambio desde servicio de facturacion',
              v_tipo_cambio,
              v_tipo_cambio,
              v_tipo_cambio,
              2,
              now(),
              p_id_usuario,
              null,
              null
              );
             end if;
             /*******************************************************/


            /***RECUPERAMOS LA ENTIDAD PARA VERIFICAR SUS PUNTOS DE VENTA***/
            select ent.id_entidad into v_id_entidad
            from param.tentidad ent
            where ent.nit = REPLACE(v_parametros.nit_entidad,' ','');
            /***************************************************************/

            IF (v_id_entidad is null) then
                Raise exception 'No se encuentra registrada la entidad con NIT: %',v_parametros.nit_entidad;

            else
            /*Aqui recuperamos el id de la sucursal*/
               select pt.id_punto_venta,
                       pt.id_sucursal
                into v_id_punto_venta,
                     v_id_sucursal
                from vef.tpunto_venta pt
                inner join vef.tsucursal su on su.id_sucursal = pt.id_sucursal
                where pt.nombre = upper(v_parametros.punto_venta) and su.id_entidad = v_id_entidad;


          if (v_id_punto_venta IS NOT NULL) then
            	select pv.codigo into v_codigo_tabla
            	from vef.tpunto_venta pv
            	where id_punto_venta = v_id_punto_venta;
          else
            	select pv.codigo into v_codigo_tabla
            	from vef.tsucursal pv
            	where id_sucursal = v_id_sucursal;
          end if;

		/*Aqui ponemos controles para verficar si existe el punto de venta y la sucursal*/
            if (v_id_punto_venta is null and v_id_sucursal is null) then
                raise exception 'El punto de venta: % no se encuentra registrado.',v_parametros.punto_venta;
            else
          /*************************Obtenemos la gestion apartir de la fecha actual***************************/
            select id_gestion into v_id_gestion
            from param.tgestion
            where gestion = extract(year from now())::integer;
          /***************************************************************************************************/
            select nextval('vef.tventa_id_venta_seq') into v_id_venta;
            v_codigo_proceso = 'VEN-' || v_id_venta;
          /************************Obtenemos el id_proceso_wf, id_estado_wf y el codigo estado*************************************/

           SELECT
              ps_num_tramite ,
              ps_id_proceso_wf ,
              ps_id_estado_wf ,
              ps_codigo_estado
            into
              v_num_tramite,
              v_id_proceso_wf,
              v_id_estado_wf,
              v_codigo_estado

            FROM wf.f_inicia_tramite(
                p_id_usuario,
                v_parametros._id_usuario_ai,
                v_parametros._nombre_usuario_ai,
                v_id_gestion,
                'VEN',
                NULL,
                NULL,
                NULL,
                v_codigo_proceso);
          /****************************************************************************************************************************/


          /*************************Obtenemso el correlativo de la venta***********************************/
              select id_periodo into v_id_periodo from
                param.tperiodo per
              where per.fecha_ini <= now()::date
                    and per.fecha_fin >=  now()::date
              limit 1 offset 0;

                      v_num_ven =   param.f_obtener_correlativo(
                      'VEN',
                      v_id_periodo,-- par_id,
                      NULL, --id_uo
                      NULL,    -- id_depto
                      p_id_usuario,
                      'VEF',
                      NULL,
                      0,
                      0,
                      (case when v_id_punto_venta is not null then
                        'vef.tpunto_venta'
                       else
                         'vef.tsucursal'
                       end),
                      (case when v_id_punto_venta is not null then
                        v_id_punto_venta
                       else
                         v_id_sucursal
                       end),
                      v_codigo_tabla
                      );
                  end if;
            end if;
        --fin obtener correlativo
              if (v_mensaje_error is null and v_mensaje_error_punto_venta is null) then

                    insert into vef.tventa(
                      id_venta,
                      id_cliente, /*Podemos poner aqui la condicion para ir insertando o no*/
                      id_sucursal, /*Nos llegaria por el servicio*/
                      id_proceso_wf, /*Se recupera para el nro de tramite*/
                      id_estado_wf, /*Se recupera para el estado que se encuentra*/
                      estado_reg,
                      nro_tramite, /*Recuperamos en la variable*/
                      a_cuenta,
                      fecha_estimada_entrega,
                      usuario_ai,
                      fecha_reg,
                      id_usuario_reg,
                      id_usuario_ai,
                      id_usuario_mod,
                      fecha_mod,
                      estado,
                      id_punto_venta, /*Llegaria desde el servicio*/
                      id_vendedor_medico,
                      porcentaje_descuento,
                      comision,
                      observaciones,
                      correlativo_venta,
                      tipo_factura,
                      fecha,
                      nro_factura,
                      id_dosificacion,
                      excento,

                      id_moneda,
                      transporte_fob,
                      seguros_fob,
                      otros_fob,
                      transporte_cif,
                      seguros_cif,
                      otros_cif,
                      tipo_cambio_venta,
                      valor_bruto,
                      descripcion_bulto,
                      nit,
                      nombre_factura,
                      id_cliente_destino,
                      hora_estimada_entrega,
                      tiene_formula,
                      forma_pedido


                    ) values(
                      v_id_venta,
                      v_id_cliente,
                      v_id_sucursal,
                      v_id_proceso_wf,
                      v_id_estado_wf,
                      'activo',
                      v_num_tramite,
                      0,
                      now(),
                      v_parametros._nombre_usuario_ai,
                      now(),
                      p_id_usuario,
                      v_parametros._id_usuario_ai,
                      null,
                      null,
                      v_codigo_estado,
                      v_id_punto_venta,
                      null,
                      0,
                      0,
                      v_parametros.observaciones,
                      v_num_ven,
                      'computarizada',
                      now(),
                      NULL,
                      null,
                      v_exento,--Excento por el momento 0
                      v_id_moneda,
                      0,
                      0,
                      0,
                      0,
                      0,
                      0,
                      0,
                      0,
                      '',
                      v_parametros.nit_cliente,
                      v_parametros.razon_social,
                      NULL,
                      v_hora_estimada,
                      'no',
                      'externa'

                    );


                    /*Aqui insertamos el detalle de la venta*/
                  for v_registros in (select *
                                      from json_populate_recordset(null::vef.detalle_venta,v_parametros.json_venta_detalle::json))loop


                  /*Aqui hacemos la conversion para que calcule con el tipo de cambio*/
                  v_precio_unitario_convertido = v_registros.precio_unitario/100;

                   if (v_id_moneda = 2) then
                      v_precio_unitario = (v_precio_unitario_convertido*v_tipo_cambio);
                  else
                      v_precio_unitario = v_precio_unitario_convertido;
                  end if;
                  /*******************************************************************/

                 insert into vef.tventa_detalle(
                  id_venta,
                  descripcion,
                  cantidad,
                  tipo,
                  estado_reg,
                  id_producto,
                  id_item,
                  --id_sucursal_producto,
                  precio,
                  id_usuario_reg,
                  fecha_reg

                  ) values(
                  v_id_venta,
                  upper(v_registros.descripcion),
                  (v_registros.cantidad/100),
                  'servicio',
                  'activo',
                  v_registros.id_concepto,
                  v_registros.id_concepto,
                  --v_parametros.id_producto,
                  v_precio_unitario,
                  p_id_usuario,
                  now()

                  )RETURNING id_venta_detalle into v_id_venta_detalle;

                  end loop;
                  /********************************/
                  select sum(ven.precio * ven.cantidad) into v_total_venta
                    from vef.tventa_detalle ven
                    where  ven.id_venta = v_id_venta;

                    update vef.tventa set
                      total_venta = v_total_venta,
                      total_venta_msuc = v_total_venta
                    where id_venta = v_id_venta;

                    /*Cuando se complete la informacion si todo va correctamente auqi obtenemos los demas datos*/
                    /*Recuperamos el id_tipo_estado y el id_estado_wf*/
                     select
                        ew.id_tipo_estado ,
                        ew.id_estado_wf,
                        ew.id_funcionario
                      into
                        v_id_tipo_estado_sig,
                        v_id_estado_wf_sig,
                        v_id_funcionario_sig
                      from wf.testado_wf ew
                        inner join wf.ttipo_estado te on te.id_tipo_estado = ew.id_tipo_estado
                      where ew.id_estado_wf =  v_id_estado_wf;
                     /********************************************************/

                     /*Obtenemos los campos de venta el id_entidad y el tipo_base*/
                      select v.*,s.id_entidad,tv.tipo_base into v_venta
                      from vef.tventa v
                        inner join vef.tsucursal s on s.id_sucursal = v.id_sucursal
                        inner join vef.tcliente c on c.id_cliente = v.id_cliente
                        inner join vef.ttipo_venta tv on tv.codigo = v.tipo_factura and tv.estado_reg = 'activo'
                      where v.id_proceso_wf = v_id_proceso_wf;
                      /***********************************************************/

                      /*Obtenemos el id del estado finalizado*/
                      v_estado_finalizado = (v_id_tipo_estado_sig+1);
                      /****************************************/

                      /*Obtenemnos el codigo finalizado*/
                      select te.codigo into v_codigo_estado
                      from wf.ttipo_estado te
                      where te.id_tipo_estado=v_estado_finalizado;
                      /******************************************/


                      /*Obtenemnos el codigo finalizado*/
                      select te.codigo into v_codigo_estado
                      from wf.ttipo_estado te
                      where te.id_tipo_estado=v_estado_finalizado;
                      /******************************************/

                      /*Creamos un nuevo parametro*/
                      v_tabla = pxp.f_crear_parametro(ARRAY[	'_nombre_usuario_ai',
                      '_id_usuario_ai',
                      'id_venta',
                      'tipo_factura',
                      'codigo_estado'],
                                                      ARRAY[	coalesce(v_parametros._nombre_usuario_ai,''),
                                                      coalesce(v_parametros._id_usuario_ai::varchar,''),
                                                      v_venta.id_venta::varchar,
                                                      v_venta.tipo_factura,
                                                      v_codigo_estado],
                                                      ARRAY[	'varchar',
                                                      'integer',
                                                      'integer',
                                                      'varchar',
                                                      'varchar']
                      );


                    /*******************************************************************************************/

                     /*Obtenemos el codigo finalizado y fin*/
                      select
                        te.codigo,te.fin
                      into
                        v_codigo_estado_siguiente,v_es_fin
                      from wf.ttipo_estado te
                      where te.id_tipo_estado = v_estado_finalizado;
                      /*********************************************************************/

                      --configurar acceso directo para la alarma
                      v_acceso_directo = '';
                      v_clase = '';
                      v_parametros_ad = '';
                      v_tipo_noti = 'notificacion';
                      v_titulo  = 'Visto Bueno';
                      v_obs = '----';

                      -- hay que recuperar el supervidor que seria el estado inmediato,...
                      v_id_estado_actual =  wf.f_registra_estado_wf(v_estado_finalizado /*tengpo*/,
                                                                    v_id_funcionario_sig/*recuperar*/,
                                                                    v_id_estado_wf /*tengo*/,
                                                                    v_id_proceso_wf/*tengo*/,
                                                                    p_id_usuario,
                                                                    v_parametros._id_usuario_ai,
                                                                    v_parametros._nombre_usuario_ai,
                                                                    v_id_depto,
                                                                    v_obs,
                                                                    v_acceso_directo ,
                                                                    v_clase,
                                                                    v_parametros_ad,
                                                                    v_tipo_noti,
                                                                    v_titulo);

                       /*Verificar que hace*/
                       IF  vef.f_fun_inicio_venta_wf(p_id_usuario,
                                                    v_parametros._id_usuario_ai,
                                                    v_parametros._nombre_usuario_ai,
                                                    v_id_estado_actual,
                                                    v_id_proceso_wf,
                                                    v_codigo_estado_siguiente) THEN

                      END IF;
                      /************************************/


                      /*Controla si hay recibos posteriores*/
                          if (v_venta.tipo_base = 'computarizada' and v_es_fin = 'si') then
                            IF v_venta.tipo_factura not in ('computarizadaexpo','computarizadaexpomin','computarizadamin') THEN
                              v_fecha_venta = now()::date;
                              if (EXISTS(	select 1
                                           from vef.tventa v
                                           where v.fecha > v_fecha_venta and v.tipo_factura = 'computarizada' and
                                                 v.estado_reg = 'activo' and v.estado = 'finalizado'))THEN
                                raise exception 'Existen recibos emitidos con fechas posterior a la actual. Por favor revise la fecha y hora del sistema';
                              end if;
                            ELSE
                              v_fecha_venta = v_venta.fecha;
                            END IF;


                            select array_agg(distinct cig.id_actividad_economica) into v_id_actividad_economica
                            from vef.tventa_detalle vd
                              inner join param.tconcepto_ingas cig on cig.id_concepto_ingas = vd.id_producto
                            where vd.id_venta = v_venta.id_venta and vd.estado_reg = 'activo';

                                  /*Aumentando esta parte*/
                                  if (v_id_actividad_economica is null)then
                                      v_id_actividad_economica = array_agg(1);
                                  end if;


                                 IF v_venta.tipo_factura not in ('computarizadaexpo','computarizadaexpomin','computarizadamin') THEN

                                    select d.* into v_dosificacion
                                    from vef.tdosificacion d
                                    where d.estado_reg = 'activo' and d.fecha_inicio_emi <= v_venta.fecha and
                                          d.fecha_limite >= v_venta.fecha and d.tipo = 'F' and d.tipo_generacion = 'computarizada' and
                                          d.id_sucursal = v_venta.id_sucursal and
                                          d.id_activida_economica @> v_id_actividad_economica FOR UPDATE;

                                    v_nro_factura = v_dosificacion.nro_siguiente;

                                    if (v_dosificacion is null) then
                                      raise exception 'No existe una dosificacion activa para emitir la factura';
                                    end if;


                                    --validar que el nro de factura no supere el maximo nro de factura de la dosificaiocn
                                    if (exists(	select 1
                                                 from vef.tventa ven
                                                 where ven.nro_factura =  v_dosificacion.nro_siguiente and ven.id_dosificacion = v_dosificacion.id_dosificacion)) then
                                      raise exception 'El numero de factura ya existe para esta dosificacion. Por favor comuniquese con el administrador del sistema';
                                    end if;

                                    --la factura de exportacion no altera la fecha
                                    update vef.tventa  set
                                      id_dosificacion = v_dosificacion.id_dosificacion,
                                      nro_factura = v_nro_factura,
                                      fecha = v_fecha_venta,
                                      cod_control = pxp.f_gen_cod_control(v_dosificacion.llave,
                                                                          v_dosificacion.nroaut,
                                                                          v_nro_factura::varchar,
                                                                          v_venta.nit,
                                                                          to_char(v_fecha_venta,'YYYYMMDD')::varchar,
                                                                          round(v_venta.total_venta,0))
                                    where id_venta = v_venta.id_venta;


                                    update vef.tdosificacion
                                    set nro_siguiente = nro_siguiente + 1
                                    where id_dosificacion = v_dosificacion.id_dosificacion;


                                  ELSE
                                    -- en las facturas de exportacion y minera  el numero se genera al inserta
                                    v_nro_factura =  v_venta.nro_factura;

                                    select
                                      *
                                    into  v_dosificacion
                                    from  vef.tdosificacion d where d.id_dosificacion = v_venta.id_dosificacion;


                                    --la factura de exportacion no altera la fecha
                                    update vef.tventa  set
                                      cod_control = pxp.f_gen_cod_control(v_dosificacion.llave,
                                                                          v_dosificacion.nroaut,
                                                                          v_nro_factura::varchar,
                                                                          v_venta.nit,
                                                                          to_char(v_fecha_venta,'YYYYMMDD')::varchar,
                                                                          round(v_venta.total_venta_msuc,0))
                                    where id_venta = v_venta.id_venta;


                                  END IF;

                            end if;
                      end if;

                /*Aqui insertamos en la alarma para que nos salga la notificacion*/
                if(v_parametros.enviar_correo = 'si') then
                    v_mensaje_correo = '<h1>Estimado usuario adjuntamos su factura</h1>';
                    v_parametros_correo = '{filtro_directo:{campo:"vef.tventa",valor:"'||v_venta.id_venta||'"}}';
                    v_documento_adjunto = 'sis_ventas_facturacion/control/Cajero/reporteFacturaCarta|'||v_venta.id_venta||'|Factura.pdf';
                    v_correos = v_parametros.correo_electronico;
				 INSERT INTO param.talarma (descripcion,
                 							acceso_directo,
                                            fecha,
                                            id_funcionario,
                                            tipo,
                                            titulo,
                                            id_usuario,
                                            titulo_correo,
                                            correos,
                                            documentos,
                                            estado_envio,
                                            estado_comunicado,
                                            pendiente,
                                            estado_notificacion,
                                            id_usuario_reg,
                                            parametros
                                            )
                							values
                						   (v_mensaje_correo,
                                           NULL,
                                           now()::date,
                                           null,
                                           'notificacion',
                                           'prueba envio de correo',
                                           p_id_usuario,
                                           'prueba envio de correo',
                                           v_correos,
                                           v_documento_adjunto,
                                           'exito',
                                           'borrador',
                                           'no',
                                           NULL,
                                           p_id_usuario,
                                           v_parametros_correo
                                           );
                 end if;


            end if;




            	--Definicion de la respuesta
                v_resp = pxp.f_agrega_clave(v_resp,'mensaje','Venta registrada correctamente');
                v_resp = pxp.f_agrega_clave(v_resp,'id_venta',v_id_venta::varchar);




            --Devuelve la respuesta
            return v_resp;

		end;

        /*********************************
        #TRANSACCION:  'VEF_ANU_FAC_EXT_ELI'
        #DESCRIPCION:	Insercion de registros
        #AUTOR: 		Ismael Valdivia
        #FECHA:	        23-05-2020
        ***********************************/

        elsif(p_transaccion='VEF_ANU_FAC_EXT_ELI')then

            begin
               select 	* into v_respaldo
               from vef.tventa ven
               inner join vef.tventa_detalle vendet on vendet.id_venta = ven.id_venta
               --inner join vef.tventa_forma_pago fp on fp.id_venta = ven.id_venta
               inner join vef.tdosificacion dos on dos.id_dosificacion = ven.id_dosificacion
               where ven.id_venta = v_parametros.id_venta;

        		insert into vef.trespaldo_facturas_anuladas (
                id_venta,
                nombre_factura,
                nit,
                cod_control,
                num_factura,
                total_venta,
                total_venta_msuc,
                id_sucursal,
                id_cliente,
                id_punto_venta,
                observaciones,
                id_moneda,
                excento,
                fecha,
                id_sucursal_producto,
                id_formula,
                id_producto,
                cantidad,
                precio,
                tipo,
                descripcion,
                --id_forma_pago,
                --monto,
                --monto_transaccion,
                --monto_mb_efectivo,
                --numero_tarjeta,
                --codigo_tarjeta,
                --tipo_tarjeta,
                --id_auxiliar,
                fecha_reg,
                id_usuario_reg,
                id_dosificacion,
                nro_autorizacion
                )
                VALUES (
                v_respaldo.id_venta,
      			v_respaldo.nombre_factura,
                v_respaldo.nit,
                v_respaldo.cod_control,
                v_respaldo.nro_factura,
                v_respaldo.total_venta,
                v_respaldo.total_venta_msuc,
                v_respaldo.id_sucursal,
                v_respaldo.id_cliente,
                v_respaldo.id_punto_venta,
                v_respaldo.observaciones,
                v_respaldo.id_moneda,
                v_respaldo.excento,
                v_respaldo.fecha,
                v_respaldo.id_sucursal_producto,
                v_respaldo.id_formula,
                v_respaldo.id_producto,
                v_respaldo.cantidad,
                v_respaldo.precio,
                v_respaldo.tipo,
                v_respaldo.descripcion,
                /*v_respaldo.id_forma_pago,
                v_respaldo.monto,
                v_respaldo.monto_transaccion,
                v_respaldo.monto_mb_efectivo,
                v_respaldo.numero_tarjeta,
                v_respaldo.codigo_tarjeta,
                v_respaldo.tipo_tarjeta,
                v_respaldo.id_auxiliar,*/
                now(),
                p_id_usuario,
                v_respaldo.id_dosificacion,
                v_respaldo.nroaut
                );

                /* update vef.tventa_forma_pago set
                monto_transaccion = 0,
                monto = 0,
                cambio = 0,
                monto_mb_efectivo = 0
                where id_venta = v_parametros.id_venta;*/

                update vef.tventa_detalle set
                precio = 0,
                cantidad = 0
                where id_venta = v_parametros.id_venta;

                update vef.tventa set
                cod_control = Null,
                total_venta_msuc = 0,
                nombre_factura = 'ANULADO',
                nit = '0',
                total_venta = 0
                where id_venta = v_parametros.id_venta;



                select * into v_venta
                from vef.tventa v
                where v.id_venta = v_parametros.id_venta;

                v_tipo_usuario = 'vendedor';

                if (v_venta.id_punto_venta is null) then
                  select  su.tipo_usuario into v_tipo_usuario
                  from vef.tsucursal_usuario su
                  where id_sucursal = v_venta.id_sucursal and su.id_usuario = p_id_usuario;
                else
                  select  su.tipo_usuario into v_tipo_usuario
                  from vef.tsucursal_usuario su
                  where su.id_punto_venta = v_venta.id_punto_venta and su.id_usuario = p_id_usuario;
                end if;

                /*if ((v_tipo_usuario = 'vendedor' and v_venta.fecha != now()::date) or p_administrador != 1) then
                  raise exception 'La venta solo puede ser anulada el mismo dia o por un administrador';
                end if;*/

                if ((v_tipo_usuario = 'vendedor' or v_tipo_usuario = 'cajero')) then
                  if (v_venta.id_usuario_reg != p_id_usuario and v_venta.fecha != now()::date ) then
                          raise exception 'La venta solo puede ser anulada el mismo dia o por un administrador';
                  end if;
                else
                    select 'administrador'::varchar as rol into v_tipo_usuario
                    from segu.tusuario_rol usurol
                    where usurol.id_usuario = p_id_usuario and usurol.estado_reg = 'activo' and (usurol.id_rol = 190 or usurol.id_rol = 1);

                    if ((v_tipo_usuario != 'administrador')) then
                        raise exception 'La venta solo puede ser anulada el mismo dia o por un administrador';
                    end if;
                end if;

                --obtenemos datos basicos
                select
                  ven.id_estado_wf,
                  ven.id_proceso_wf,
                  ven.estado,
                  ven.id_venta,
                  ven.nro_tramite
                into
                  v_registros
                from vef.tventa ven
                where ven.id_venta = v_parametros.id_venta;

                /*Obtenemos los campos de venta el id_entidad y el tipo_base*/
                select v.* into v_venta
                from vef.tventa v
                where v.id_venta = v_parametros.id_venta;
                /***********************************************************/

                v_res = vef.f_anula_venta(p_administrador,p_id_usuario,p_tabla, v_registros.id_proceso_wf,v_registros.id_estado_wf, v_parametros.id_venta);


                --Definicion de la respuesta
                v_resp = pxp.f_agrega_clave(v_resp,'mensaje','Venta anulada(a)');
                v_resp = pxp.f_agrega_clave(v_resp,'Anulacion','Anulacion correctamente');

                --Devuelve la respuesta
                return v_resp;

            end;

	/*********************************
 	#TRANSACCION:  'VEF_INS_FAC_EXT_MOD'
 	#DESCRIPCION:	Insercion de registros
    #AUTOR: 		Ismael Valdivia
    #FECHA:	        23-05-2020
	***********************************/

	elsif(p_transaccion='VEF_INS_FAC_EXT_MOD')then

		begin
			--Sentencia de la modificacion
			update cola.tprioridad set
			nombre = v_parametros.nombre,
   			sigla = v_parametros.sigla,
			descripcion = v_parametros.descripcion,
			estado = v_parametros.estado,
			peso = v_parametros.peso,
			fecha_mod = now(),
			id_usuario_mod = p_id_usuario,
			id_usuario_ai = v_parametros._id_usuario_ai,
			usuario_ai = v_parametros._nombre_usuario_ai
			where id_prioridad=v_parametros.id_prioridad;

			--Definicion de la respuesta
            v_resp = pxp.f_agrega_clave(v_resp,'mensaje','Prioridad modificado(a)');
            v_resp = pxp.f_agrega_clave(v_resp,'id_prioridad',v_parametros.id_prioridad::varchar);

            --Devuelve la respuesta
            return v_resp;

		end;

	/*********************************
 	#TRANSACCION:  'VEF_INS_FAC_EXT_ELI'
 	#DESCRIPCION:	Insercion de registros
    #AUTOR: 		Ismael Valdivia
    #FECHA:	        23-05-2020
	***********************************/

	elsif(p_transaccion='VEF_INS_FAC_EXT_ELI')then

		begin
			--Sentencia de la eliminacion
			delete from cola.tprioridad
            where id_prioridad=v_parametros.id_prioridad;

            --Definicion de la respuesta
            v_resp = pxp.f_agrega_clave(v_resp,'mensaje','Prioridad eliminado(a)');
            v_resp = pxp.f_agrega_clave(v_resp,'id_prioridad',v_parametros.id_prioridad::varchar);

            --Devuelve la respuesta
            return v_resp;

		end;

	else

    	raise exception 'Transaccion inexistente: %',p_transaccion;

	end if;

EXCEPTION

	WHEN OTHERS THEN
		v_resp='';
		v_resp = pxp.f_agrega_clave(v_resp,'mensaje',SQLERRM);
		v_resp = pxp.f_agrega_clave(v_resp,'codigo_error',SQLSTATE);
		v_resp = pxp.f_agrega_clave(v_resp,'procedimientos',v_nombre_funcion);
		raise exception '%',v_resp;

END;
$body$
LANGUAGE 'plpgsql'
VOLATILE
CALLED ON NULL INPUT
SECURITY INVOKER
COST 100;

ALTER FUNCTION vef.ft_facturacion_externa_ime (p_administrador integer, p_id_usuario integer, p_tabla varchar, p_transaccion varchar)
  OWNER TO postgres;
