<?php /* Smarty version 2.6.26, created on 2011-09-01 05:31:20
         compiled from backend/google_api/gmaps_ext_popup.html */ ?>
<script type="text/javascript" src="<?php echo @URL_JS; ?>
/JsHttpRequest.js"></script>
<script language="JavaScript" type="text/javascript">
<?php echo '
window.alert = function() { return false; };
'; ?>

</script>

<?php if (@CONF_GOOGLE_MAPS_API_KEY != ''): ?>
<script type="text/javascript" language="JavaScript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo @CONF_GOOGLE_MAPS_API_KEY; ?>
"></script>
<?php endif; ?>
<script type="text/javascript" language="JavaScript">
<!--
<?php echo '

var map_win;
var current_show_addr;
var rd_win;
var render_from_ca = false;

function showMapWindow(addr)
{
	G_INCOMPAT = false;
    current_show_addr = addr;
    render_from_ca = false;
    // create the window on the first click and reuse on subsequent clicks
    if(!map_win){
        map_win = new Ext.Window({
            el: \''; 
 echo $this->_tpl_vars['map_win_name']; 
 echo '\',
            layout: \'fit\',
            width: 707,
            height: 500,
            closeAction: \'hide\',
            plain: true,
            autoScroll: false,
            resizable: false,
            modal: true,
            
            tbar: [
             {
                text: \''; 
 echo 'Изменить адрес'; 
 echo '\'
               ,handler: function() { 
                       Ext.Msg.show({
                               title: \''; 
 echo 'Адрес'; 
 echo '\',
                               value: current_show_addr,
                               width: 300,
                               buttons: {\'ok\': \''; 
 echo 'OK'; 
 echo '\', \'cancel\': \''; 
 echo 'Отмена'; 
 echo '\'},
                               multiline: true,
                               icon: Ext.MessageBox.QUESTION,
                               fn: function(btn, text) {
                                current_show_addr = text;
                                render_from_ca = true;
                                if(btn == \'ok\') renderMapForAddress(text.replace("\\n"," "));
                               }
                        });       
                 }
               ,disabled: ('; ?>
'<?php echo @CONF_GOOGLE_MAPS_API_KEY; ?>
'<?php echo ' == \'\' || G_INCOMPAT ? true : false)
             }
            ,{
                text: \''; 
 echo 'Проложить маршрут'; 
 echo '\'
               ,handler: function() { showRDWindow(); }
               ,disabled: ('; ?>
'<?php echo @CONF_GOOGLE_MAPS_API_KEY; ?>
'<?php echo ' == \'\' || G_INCOMPAT ? true : false)
             }
            ,{
                text: \''; 
 echo 'Печать'; 
 echo '\'
               ,handler: function() {
                    var map_center = map.getCenter().toUrlValue();
                    window.open(\'http://maps.google.com/maps?ll=\'+map_center+\'&z=\'+map.getZoom()+\'&key='; 
 echo @CONF_GOOGLE_MAPS_API_KEY; 
 echo '&pw=2\');
               }
               ,disabled: ('; ?>
'<?php echo @CONF_GOOGLE_MAPS_API_KEY; ?>
'<?php echo ' == \'\' || G_INCOMPAT ? true : false)
             }
            ,{
                text: \'\'
               ,minWidth: 50
               ,disabled: true
             }
            ,{
                text: \''; 
 echo 'Закрыть'; 
 echo '\',
                handler: function(){
                    map_win.hide();
                }
             }
            ]
        });
    }
    map_win.show(map_win);
    
    if(\''; 
 echo @CONF_GOOGLE_MAPS_API_KEY; 
 echo '\' != \'\')
    {
        if(map == null)
        {
            gmap_initialize();
        };
        renderMapForAddress(addr);
        return;
    };    
    
};

function showRDWindow()
{
    if(!rd_win)
    {
        rd_win = new Ext.Window({
            el: \''; 
 echo $this->_tpl_vars['map_win_name']; 
 echo '_rd\',
            layout: \'fit\',
            width: 307,
            height: 220,
            closeAction: \'hide\',
            plain: true,
            autoScroll: false,
            resizable: false,
            modal: true,
            buttonAlign: \'center\',
            buttons: [
                {
                    text: \''; 
 echo 'Проложить'; 
 echo '\'
                   ,handler: function() { renderDirection(); }
                }
               ,{
                    text: \''; 
 echo 'Отмена'; 
 echo '\'
                   ,handler: function() { rd_win.hide(); }
                }
            ]
        });
    };
    
    document.getElementById(\''; 
 echo $this->_tpl_vars['map_win_name']; 
 echo '_rd_to\').innerHTML = current_show_addr;
    rd_win.show(rd_win);
};

var map = null;
var geocoder = null;

function gmap_initialize()
{
  if(G_INCOMPAT)
  {
    var el = document.getElementById(\''; 
 echo $this->_tpl_vars['map_canvas_name']; 
 echo '\');
    var _html = \'<center style="padding: 150px 20px 0px 20px;">\';
    _html += \''; 
 echo 'Неверный ключ Google Maps API.'; 
 echo '\';
    _html += \'<br\\/><br\\/><br\\/>\';
    _html += \''; 
 echo 'Введите ключ Google Maps API для доменного имени, на котором работает магазин'; 
 echo ': <input type="text" id="gmapi_key_val" value="" size="40" \\/>\';
    _html += \'<button id="gmapi_key_sbut" type="button" onClick="checkGMAPIKey();">'; 
 echo 'Сохранить'; 
 echo '<\\/button>\';
    _html += \'<\\/center>\';
    el.innerHTML = _html;
    return false;
  };
  if (GBrowserIsCompatible())
  {
    map = new GMap2(document.getElementById("'; 
 echo $this->_tpl_vars['map_canvas_name']; 
 echo '"), {size: new GSize(700,500)});
    map.enableScrollWheelZoom();
    var mapControl = new GMapTypeControl();
    map.addControl(mapControl);
    map.addControl(new GLargeMapControl());
    geocoder = new GClientGeocoder();
  }
};

function renderMapForAddress(address)
{
  if (geocoder)
  {
    geocoder.getLatLng(
      address,
      function(point) {
        if (!point) {
            Ext.Msg.show({
               title: \''; 
 echo 'Адрес'; 
 echo '\',
               msg: \''; 
 echo 'Не найдено'; 
 echo '\',
               value: address,
               width: 300,
               buttons: {\'ok\': \''; 
 echo 'Повторить поиск'; 
 echo '\', \'cancel\': \''; 
 echo 'Отмена'; 
 echo '\'},
               multiline: true,
               icon: Ext.MessageBox.WARNING,
               fn: function(btn, text) {
                current_show_addr = text;
                if(btn == \'ok\') renderMapForAddress(text.replace("\\n"," "));
                else if(!render_from_ca) map_win.hide();
               }
            });       
      } else {
          map.setCenter(point, 13);
          var marker = new GMarker(point);
          map.addOverlay(marker);
          marker.openInfoWindowHtml(address);
        }
      }
    );
  }
};

var directions = null;
var can_make_route_from = null;
var can_make_route_to = null;

function renderDirection()
{
    if(directions == null)
        directions = new GDirections(map);
    else
        directions.clear();
    
    var addr_from = document.getElementById(\''; 
 echo $this->_tpl_vars['map_win_name']; 
 echo '_rd_from\').value.replace(/\\s+/g,\' \');
    var addr_to = document.getElementById(\''; 
 echo $this->_tpl_vars['map_win_name']; 
 echo '_rd_to\').value.replace(/\\s+/g,\' \');

    GEvent.addListener(directions, "load", onGDirectionsLoad);
    GEvent.addListener(directions, "error", handleErrors);
    //directions.load(addr_from+\' to \'+addr_to);
    directions.load(\'from: \'+addr_from+\' to: \'+addr_to);
};

function handleErrors()
{
   switch(directions.getStatus().code)
   {
        case G_GEO_UNKNOWN_ADDRESS:
                emsg = \''; 
 echo 'Указанный адрес не найден на картах Google. Адрес задан неверно или не зарегистрирован в базе данных Google Maps.'; 
 echo '\'; break;
        case G_GEO_SERVER_ERROR:
                emsg = \''; 
 echo 'Произошла неопознанная ошибка обработки запроса на стороне Google Maps.'; 
 echo '\'; break;
        case G_GEO_MISSING_QUERY:
                emsg = \''; 
 echo 'Не задан адрес для поиска.'; 
 echo '\'; break;
        case G_GEO_BAD_KEY:
                emsg = \''; 
 echo 'Ключ Google Maps API введен неверно или не соответствует доменному имени, для которого был создан.'; 
 echo '\'; break;
        case G_GEO_BAD_REQUEST:
                emsg = \''; 
 echo 'Ошибка обработки запроса с данными маршрута.'; 
 echo '\'; break;
        default: emsg = \''; 
 echo 'Произошла неопознанная ошибка.'; 
 echo '\'; break;
   };

   Ext.Msg.show({
        buttons: Ext.MessageBox.OK
       ,icon: Ext.MessageBox.ERROR
       ,title: \''; 
 echo 'Ошибка'; 
 echo '\'
       ,msg: emsg
     });
   
};

function onGDirectionsLoad()
{
    if(directions.getStatus().code == G_GEO_SUCCESS)
    {
        rd_win.hide();
        
        if(\''; 
 echo @CONF_WAREHOUSE_ADDRESS; 
 echo '\' == \'\' && !addr_cfg_saved)
        {
            saveAddrCfg();
        };
    };
}; 

var addr_cfg_saved = false;
function saveAddrCfg()
{
    var addr_cfg = document.getElementById(\''; 
 echo $this->_tpl_vars['map_win_name']; 
 echo '_rd_from\').value;

    var req = new JsHttpRequest();
    
    req.onreadystatechange = function()
    {
        if (req.readyState != 4) return;
        if(req.responseText) alert(req.responseText);
        
        addr_cfg_saved = true;
    };
    
    try
    {
        req.open(null, set_query(\'&caller=1&initscript=ajaxservice&ukey=configuration\'), true);
        req.send({\'action\': \'ajax_set_setting\', \'setting_name\': \'CONF_WAREHOUSE_ADDRESS\', \'setting_value\': addr_cfg});
    }
    catch ( e )
    {
      catchResult(e);
    }
    finally { ;}
};

function checkGMAPIKey()
{
var field_el = document.getElementById(\'gmapi_key_val\');
    
    if(field_el.value == \'\')
    {
       Ext.Msg.show({
            buttons: Ext.MessageBox.OK
           ,icon: Ext.MessageBox.ERROR
           ,title: \''; 
 echo 'Ошибка'; 
 echo '\'
           ,msg: \''; 
 echo 'Неверный ключ Google Maps API.'; 
 echo '\'
         });
        field_el.focus();
        return;
    };
    
    if(!document.getElementById(\'gmapi_check_iframe\'))
    {
        var gmf = document.createElement(\'IFRAME\');
        gmf.id = \'gmapi_check_iframe\';
        gmf.frameborder = 0;
        gmf.height = 0;
        gmf.width = 0;
        gmf.marginheight = 0;
        gmf.marginwidth = 0;
        gmf.scrolling = \'no\';
        gmf.style.width = \'0px\';
        gmf.style.height = \'0px\';
        gmf.style.border = \'0px\';
        gmf.style.visibility = \'hidden\';
        gmf.style.position = \'absolute\';
        document.body.appendChild(gmf);
    }
    else
    {
        var gmf = document.getElementById(\'gmapi_check_iframe\');
    };
    
    Ext.Msg.wait(\''; 
 echo 'Пожалуйста, подождите'; 
 echo '\', \''; 
 echo 'Проверка ключа Google Maps API'; 
 echo '\');
    
    gmf.src = \'index.php?ukey=gmapi_key_checker&gmapi_key=\'+field_el.value;
};

function handleGMAPIKeyChecker(is_correct)
{
    var field_el = document.getElementById(\'gmapi_key_val\');
    
    if(is_correct)
    {
        saveGMAPIKey(field_el.value);
    }
    else
    {
       Ext.Msg.hide();
       Ext.Msg.show({
            buttons: Ext.MessageBox.OK
           ,icon: Ext.MessageBox.ERROR
           ,title: \''; 
 echo 'Ошибка'; 
 echo '\'
           ,msg: \''; 
 echo 'Неверный ключ Google Maps API.'; 
 echo '\'
         });
        field_el.focus();
    };
};

function saveGMAPIKey(key_value)
{
    var req = new JsHttpRequest();
    
    req.onreadystatechange = function()
    {
        if (req.readyState != 4) return;
        if(req.responseText) alert(req.responseText);
        
        window.location.reload();
    };
    
    try
    {
        req.open(null, set_query(\'&caller=1&initscript=ajaxservice&ukey=configuration\'), true);
        req.send({\'action\': \'ajax_set_setting\', \'setting_name\': \'CONF_GOOGLE_MAPS_API_KEY\', \'setting_value\': key_value});
    }
    catch ( e )
    {
      catchResult(e);
    }
    finally { ;}
};

'; ?>

//-->
</script>

<div id="<?php echo $this->_tpl_vars['map_win_name']; ?>
" class="x-hidden">
    <div class="x-window-header"><?php echo 'Найти адрес на карте'; ?>
</div>
    <div class="x-window-body" id="<?php echo $this->_tpl_vars['map_canvas_name']; ?>
">
    <?php if (@CONF_GOOGLE_MAPS_API_KEY == ''): ?>
    <center style="padding: 150px 20px 0px 20px;">
    <?php echo 'Введите ключ Google Maps API для доменного имени, на котором установлен ваш магазин, чтобы включить возможность просмотра адресов на карте. Вы можете <a href="http://code.google.com/apis/maps/signup.html" target="_blank">зарегистрировать ключ Google Maps API</a> бесплатно на сайте Google.'; ?>

    <br/><br/><br/>
    <?php echo 'Введите ключ Google Maps API для доменного имени, на котором работает магазин'; ?>
: <input type="text" id="gmapi_key_val" value="" size="40" />
    <button id="gmapi_key_sbut" type="button" onClick="checkGMAPIKey();"><?php echo 'Сохранить'; ?>
</button>
    </center>
    <?php endif; ?>
    </div>
</div>

<div id="<?php echo $this->_tpl_vars['map_win_name']; ?>
_rd" class="x-hidden">
    <div class="x-window-header"><?php echo 'Проложить маршрут'; ?>
</div>
    <div class="x-window-body" id="<?php echo $this->_tpl_vars['map_canvas_name']; ?>
_rd">
        <table>
            <tr>
                <td valign="top" align="right"><?php echo 'Откуда'; ?>
</td>
                <td>
                    <textarea id="<?php echo $this->_tpl_vars['map_win_name']; ?>
_rd_from" cols="35" rows="3" style="border: solid 1px black;"><?php if (@CONF_WAREHOUSE_ADDRESS != ''): 
 echo @CONF_WAREHOUSE_ADDRESS; 
 else: 
 echo 'Введите адрес, откуда вы доставляете заказ'; 
 endif; ?></textarea>
                </td>
            </tr>
            <tr>
                <td valign="top" align="right"><?php echo 'Куда'; ?>
</td>
                <td>
                    <textarea id="<?php echo $this->_tpl_vars['map_win_name']; ?>
_rd_to" cols="35" rows="3" style="border: solid 1px black;"></textarea>
                </td>
            </tr>
        </table>
    </div>
</div>