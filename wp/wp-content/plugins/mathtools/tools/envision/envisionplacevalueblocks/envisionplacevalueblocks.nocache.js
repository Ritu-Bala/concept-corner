function envisionplacevalueblocks(){
  var $wnd_0 = window;
  var $doc_0 = document;
  sendStats('bootstrap', 'begin');
  function isHostedMode(){
    var query = $wnd_0.location.search;
    return query.indexOf('gwt.codesvr.envisionplacevalueblocks=') != -1 || query.indexOf('gwt.codesvr=') != -1;
  }

  function sendStats(evtGroupString, typeString){
    if ($wnd_0.__gwtStatsEvent) {
      $wnd_0.__gwtStatsEvent({moduleName:'envisionplacevalueblocks', sessionId:$wnd_0.__gwtStatsSessionId, subSystem:'startup', evtGroup:evtGroupString, millis:(new Date).getTime(), type:typeString});
    }
  }

  envisionplacevalueblocks.__sendStats = sendStats;
  envisionplacevalueblocks.__moduleName = 'envisionplacevalueblocks';
  envisionplacevalueblocks.__errFn = null;
  envisionplacevalueblocks.__moduleBase = 'DUMMY';
  envisionplacevalueblocks.__softPermutationId = 0;
  envisionplacevalueblocks.__computePropValue = null;
  envisionplacevalueblocks.__getPropMap = null;
  envisionplacevalueblocks.__gwtInstallCode = function(){
  }
  ;
  envisionplacevalueblocks.__gwtStartLoadingFragment = function(){
    return null;
  }
  ;
  var __gwt_isKnownPropertyValue = function(){
    return false;
  }
  ;
  var __gwt_getMetaProperty = function(){
    return null;
  }
  ;
  __propertyErrorFunction = null;
  var activeModules = $wnd_0.__gwt_activeModules = $wnd_0.__gwt_activeModules || {};
  activeModules['envisionplacevalueblocks'] = {moduleName:'envisionplacevalueblocks'};
  var frameDoc;
  function getInstallLocationDoc(){
    setupInstallLocation();
    return frameDoc;
  }

  function getInstallLocation(){
    setupInstallLocation();
    return frameDoc.getElementsByTagName('body')[0];
  }

  function setupInstallLocation(){
    if (frameDoc) {
      return;
    }
    var scriptFrame = $doc_0.createElement('iframe');
    scriptFrame.src = 'javascript:""';
    scriptFrame.id = 'envisionplacevalueblocks';
    scriptFrame.style.cssText = 'position:absolute; width:0; height:0; border:none; left: -1000px;' + ' top: -1000px;';
    scriptFrame.tabIndex = -1;
    $doc_0.body.appendChild(scriptFrame);
    frameDoc = scriptFrame.contentDocument;
    if (!frameDoc) {
      frameDoc = scriptFrame.contentWindow.document;
    }
    frameDoc.open();
    var doctype = document.compatMode == 'CSS1Compat'?'<!doctype html>':'';
    frameDoc.write(doctype + '<html><head><\/head><body><\/body><\/html>');
    frameDoc.close();
  }

  function installScript(filename){
    function setupWaitForBodyLoad(callback){
      function isBodyLoaded(){
        if (typeof $doc_0.readyState == 'undefined') {
          return typeof $doc_0.body != 'undefined' && $doc_0.body != null;
        }
        return /loaded|complete/.test($doc_0.readyState);
      }

      var bodyDone = isBodyLoaded();
      if (bodyDone) {
        callback();
        return;
      }
      function onBodyDone(){
        if (!bodyDone) {
          bodyDone = true;
          callback();
          if ($doc_0.removeEventListener) {
            $doc_0.removeEventListener('DOMContentLoaded', onBodyDone, false);
          }
          if (onBodyDoneTimerId) {
            clearInterval(onBodyDoneTimerId);
          }
        }
      }

      if ($doc_0.addEventListener) {
        $doc_0.addEventListener('DOMContentLoaded', onBodyDone, false);
      }
      var onBodyDoneTimerId = setInterval(function(){
        if (isBodyLoaded()) {
          onBodyDone();
        }
      }
      , 50);
    }

    function installCode(code){
      function removeScript(body, element){
      }

      var docbody = getInstallLocation();
      var doc = getInstallLocationDoc();
      var script;
      if (navigator.userAgent.indexOf('Chrome') > -1 && window.JSON) {
        var scriptFrag = doc.createDocumentFragment();
        scriptFrag.appendChild(doc.createTextNode('eval("'));
        for (var i = 0; i < code.length; i++) {
          var c = window.JSON.stringify(code[i]);
          scriptFrag.appendChild(doc.createTextNode(c.substring(1, c.length - 1)));
        }
        scriptFrag.appendChild(doc.createTextNode('");'));
        script = doc.createElement('script');
        script.language = 'javascript';
        script.appendChild(scriptFrag);
        docbody.appendChild(script);
        removeScript(docbody, script);
      }
       else {
        for (var i = 0; i < code.length; i++) {
          script = doc.createElement('script');
          script.language = 'javascript';
          script.text = code[i];
          docbody.appendChild(script);
          removeScript(docbody, script);
        }
      }
    }

    envisionplacevalueblocks.onScriptDownloaded = function(code){
      setupWaitForBodyLoad(function(){
        installCode(code);
      }
      );
    }
    ;
    sendStats('moduleStartup', 'moduleRequested');
    var script_0 = $doc_0.createElement('script');
    script_0.src = filename;
    $doc_0.getElementsByTagName('head')[0].appendChild(script_0);
  }

  envisionplacevalueblocks.__startLoadingFragment = function(fragmentFile){
    return computeUrlForResource(fragmentFile);
  }
  ;
  envisionplacevalueblocks.__installRunAsyncCode = function(code){
    var docbody = getInstallLocation();
    var script = getInstallLocationDoc().createElement('script');
    script.language = 'javascript';
    script.text = code;
    docbody.appendChild(script);
  }
  ;
  function processMetas(){
    var metaProps = {};
    var propertyErrorFunc;
    var onLoadErrorFunc;
    var metas = $doc_0.getElementsByTagName('meta');
    for (var i = 0, n = metas.length; i < n; ++i) {
      var meta = metas[i], name_1 = meta.getAttribute('name'), content_0;
      if (name_1) {
        name_1 = name_1.replace('envisionplacevalueblocks::', '');
        if (name_1.indexOf('::') >= 0) {
          continue;
        }
        if (name_1 == 'gwt:property') {
          content_0 = meta.getAttribute('content');
          if (content_0) {
            var value_0, eq = content_0.indexOf('=');
            if (eq >= 0) {
              name_1 = content_0.substring(0, eq);
              value_0 = content_0.substring(eq + 1);
            }
             else {
              name_1 = content_0;
              value_0 = '';
            }
            metaProps[name_1] = value_0;
          }
        }
         else if (name_1 == 'gwt:onPropertyErrorFn') {
          content_0 = meta.getAttribute('content');
          if (content_0) {
            try {
              propertyErrorFunc = eval(content_0);
            }
             catch (e) {
              alert('Bad handler "' + content_0 + '" for "gwt:onPropertyErrorFn"');
            }
          }
        }
         else if (name_1 == 'gwt:onLoadErrorFn') {
          content_0 = meta.getAttribute('content');
          if (content_0) {
            try {
              onLoadErrorFunc = eval(content_0);
            }
             catch (e) {
              alert('Bad handler "' + content_0 + '" for "gwt:onLoadErrorFn"');
            }
          }
        }
      }
    }
    __gwt_getMetaProperty = function(name_0){
      var value = metaProps[name_0];
      return value == null?null:value;
    }
    ;
    __propertyErrorFunction = propertyErrorFunc;
    envisionplacevalueblocks.__errFn = onLoadErrorFunc;
  }

  function computeScriptBase(){
    function getDirectoryOfFile(path){
      var hashIndex = path.lastIndexOf('#');
      if (hashIndex == -1) {
        hashIndex = path.length;
      }
      var queryIndex = path.indexOf('?');
      if (queryIndex == -1) {
        queryIndex = path.length;
      }
      var slashIndex = path.lastIndexOf('/', Math.min(queryIndex, hashIndex));
      return slashIndex >= 0?path.substring(0, slashIndex + 1):'';
    }

    function ensureAbsoluteUrl(url){
      if (url.match(/^\w+:\/\//)) {
      }
       else {
        var img = $doc_0.createElement('img');
        img.src = url + 'clear.cache.gif';
        url = getDirectoryOfFile(img.src);
      }
      return url;
    }

    function tryMetaTag(){
      var metaVal = __gwt_getMetaProperty('baseUrl');
      if (metaVal != null) {
        return metaVal;
      }
      return '';
    }

    function tryNocacheJsTag(){
      var scriptTags = $doc_0.getElementsByTagName('script');
      for (var i = 0; i < scriptTags.length; ++i) {
        if (scriptTags[i].src.indexOf('envisionplacevalueblocks.nocache.js') != -1) {
          return getDirectoryOfFile(scriptTags[i].src);
        }
      }
      return '';
    }

    function tryBaseTag(){
      var baseElements = $doc_0.getElementsByTagName('base');
      if (baseElements.length > 0) {
        return baseElements[baseElements.length - 1].href;
      }
      return '';
    }

    function isLocationOk(){
      var loc = $doc_0.location;
      return loc.href == loc.protocol + '//' + loc.host + loc.pathname + loc.search + loc.hash;
    }

    var tempBase = tryMetaTag();
    if (tempBase == '') {
      tempBase = tryNocacheJsTag();
    }
    if (tempBase == '') {
      tempBase = tryBaseTag();
    }
    if (tempBase == '' && isLocationOk()) {
      tempBase = getDirectoryOfFile($doc_0.location.href);
    }
    tempBase = ensureAbsoluteUrl(tempBase);
    return tempBase;
  }

  function computeUrlForResource(resource){
    if (resource.match(/^\//)) {
      return resource;
    }
    if (resource.match(/^[a-zA-Z]+:\/\//)) {
      return resource;
    }
    return envisionplacevalueblocks.__moduleBase + resource;
  }

  function getCompiledCodeFilename(){
    var answers = [];
    var softPermutationId;
    function unflattenKeylistIntoAnswers(propValArray, value){
      var answer = answers;
      for (var i = 0, n = propValArray.length - 1; i < n; ++i) {
        answer = answer[propValArray[i]] || (answer[propValArray[i]] = []);
      }
      answer[propValArray[n]] = value;
    }

    var values = [];
    var providers = [];
    function computePropValue(propName){
      var value = providers[propName](), allowedValuesMap = values[propName];
      if (value in allowedValuesMap) {
        return value;
      }
      var allowedValuesList = [];
      for (var k in allowedValuesMap) {
        allowedValuesList[allowedValuesMap[k]] = k;
      }
      if (__propertyErrorFunc) {
        __propertyErrorFunc(propName, allowedValuesList, value);
      }
      throw null;
    }

    providers['formfactor'] = function(){
      var args = location.search;
      var start = args.indexOf('formfactor');
      if (start >= 0) {
        var value = args.substring(start);
        var begin = value.indexOf('=') + 1;
        var end = value.indexOf('&');
        if (end == -1) {
          end = value.length;
        }
        return value.substring(begin, end);
      }
      var ua = navigator.userAgent.toLowerCase();
      if (ua.indexOf('iphone') != -1 || (ua.indexOf('ipod') != -1 || ua.indexOf('ipad') != -1)) {
        return 'ios';
      }
      return 'desktop';
    }
    ;
    values['formfactor'] = {desktop:0, ios:1};
    providers['locale'] = function(){
      var locale = null;
      var rtlocale = 'default';
      try {
        if (!locale) {
          var queryParam = location.search;
          var qpStart = queryParam.indexOf('locale=');
          if (qpStart >= 0) {
            var value = queryParam.substring(qpStart + 7);
            var end = queryParam.indexOf('&', qpStart);
            if (end < 0) {
              end = queryParam.length;
            }
            locale = queryParam.substring(qpStart + 7, end);
          }
        }
        if (!locale) {
          locale = __gwt_getMetaProperty('locale');
        }
        if (!locale) {
          locale = $wnd_0['__gwt_Locale'];
        }
        if (locale) {
          rtlocale = locale;
        }
        while (locale && !__gwt_isKnownPropertyValue('locale', locale)) {
          var lastIndex = locale.lastIndexOf('_');
          if (lastIndex < 0) {
            locale = null;
            break;
          }
          locale = locale.substring(0, lastIndex);
        }
      }
       catch (e) {
        alert('Unexpected exception in locale detection, using default: ' + e);
      }
      $wnd_0['__gwt_Locale'] = rtlocale;
      return locale || 'default';
    }
    ;
    values['locale'] = {'default':0, es_US:1};
    providers['user.agent'] = function(){
      var ua = navigator.userAgent.toLowerCase();
      var makeVersion = function(result){
        return parseInt(result[1]) * 1000 + parseInt(result[2]);
      }
      ;
      if (function(){
        return ua.indexOf('opera') != -1;
      }
      ())
        return 'opera';
      if (function(){
        return ua.indexOf('webkit') != -1;
      }
      ())
        return 'safari';
      if (function(){
        return ua.indexOf('msie') != -1 && $doc_0.documentMode >= 9;
      }
      ())
        return 'ie9';
      if (function(){
        return ua.indexOf('msie') != -1 && $doc_0.documentMode >= 8;
      }
      ())
        return 'ie8';
      if (function(){
        var result = /msie ([0-9]+)\.([0-9]+)/.exec(ua);
        if (result && result.length == 3)
          return makeVersion(result) >= 6000;
      }
      ())
        return 'ie6';
      if (function(){
        return ua.indexOf('gecko') != -1;
      }
      ())
        return 'gecko1_8';
      return 'unknown';
    }
    ;
    values['user.agent'] = {gecko1_8:0, ie6:1, ie8:2, ie9:3, opera:4, safari:5};
    __gwt_isKnownPropertyValue = function(propName, propValue){
      return propValue in values[propName];
    }
    ;
    envisionplacevalueblocks.__getPropMap = function(){
      var result = {};
      for (var key in values) {
        if (values.hasOwnProperty(key)) {
          result[key] = computePropValue(key);
        }
      }
      return result;
    }
    ;
    envisionplacevalueblocks.__computePropValue = computePropValue;
    $wnd_0.__gwt_activeModules['envisionplacevalueblocks'].bindings = envisionplacevalueblocks.__getPropMap;
    sendStats('bootstrap', 'selectingPermutation');
    if (isHostedMode()) {
      return computeUrlForResource('envisionplacevalueblocks.devmode.js');
    }
    var strongName;
    try {
      unflattenKeylistIntoAnswers(['desktop', 'es_US', 'gecko1_8'], '190C431963842CE32215F01D03C38698');
      unflattenKeylistIntoAnswers(['ios', 'es_US', 'gecko1_8'], '190C431963842CE32215F01D03C38698');
      unflattenKeylistIntoAnswers(['desktop', 'es_US', 'gecko1_8'], '190C431963842CE32215F01D03C38698' + ':1');
      unflattenKeylistIntoAnswers(['ios', 'es_US', 'gecko1_8'], '190C431963842CE32215F01D03C38698' + ':1');
      unflattenKeylistIntoAnswers(['desktop', 'es_US', 'ie9'], '39EAD03C670D6F8B52B6D9A1C0C7DB94');
      unflattenKeylistIntoAnswers(['ios', 'es_US', 'ie9'], '39EAD03C670D6F8B52B6D9A1C0C7DB94');
      unflattenKeylistIntoAnswers(['desktop', 'es_US', 'ie9'], '39EAD03C670D6F8B52B6D9A1C0C7DB94' + ':1');
      unflattenKeylistIntoAnswers(['ios', 'es_US', 'ie9'], '39EAD03C670D6F8B52B6D9A1C0C7DB94' + ':1');
      unflattenKeylistIntoAnswers(['desktop', 'default', 'safari'], '66933FA81C3039ADD5A11B949A935EFE');
      unflattenKeylistIntoAnswers(['ios', 'default', 'safari'], '66933FA81C3039ADD5A11B949A935EFE');
      unflattenKeylistIntoAnswers(['desktop', 'default', 'safari'], '66933FA81C3039ADD5A11B949A935EFE' + ':1');
      unflattenKeylistIntoAnswers(['ios', 'default', 'safari'], '66933FA81C3039ADD5A11B949A935EFE' + ':1');
      unflattenKeylistIntoAnswers(['desktop', 'es_US', 'safari'], '8BF4CA6AB0C6B4573CBB13E65A77792F');
      unflattenKeylistIntoAnswers(['ios', 'es_US', 'safari'], '8BF4CA6AB0C6B4573CBB13E65A77792F');
      unflattenKeylistIntoAnswers(['desktop', 'es_US', 'safari'], '8BF4CA6AB0C6B4573CBB13E65A77792F' + ':1');
      unflattenKeylistIntoAnswers(['ios', 'es_US', 'safari'], '8BF4CA6AB0C6B4573CBB13E65A77792F' + ':1');
      unflattenKeylistIntoAnswers(['desktop', 'default', 'gecko1_8'], 'A696192C3D68B15805DBE7A24EC7BA1C');
      unflattenKeylistIntoAnswers(['ios', 'default', 'gecko1_8'], 'A696192C3D68B15805DBE7A24EC7BA1C');
      unflattenKeylistIntoAnswers(['desktop', 'default', 'gecko1_8'], 'A696192C3D68B15805DBE7A24EC7BA1C' + ':1');
      unflattenKeylistIntoAnswers(['ios', 'default', 'gecko1_8'], 'A696192C3D68B15805DBE7A24EC7BA1C' + ':1');
      unflattenKeylistIntoAnswers(['desktop', 'default', 'ie9'], 'C20FF430E04B4A31547F3D43A2E8099E');
      unflattenKeylistIntoAnswers(['ios', 'default', 'ie9'], 'C20FF430E04B4A31547F3D43A2E8099E');
      unflattenKeylistIntoAnswers(['desktop', 'default', 'ie9'], 'C20FF430E04B4A31547F3D43A2E8099E' + ':1');
      unflattenKeylistIntoAnswers(['ios', 'default', 'ie9'], 'C20FF430E04B4A31547F3D43A2E8099E' + ':1');
      strongName = answers[computePropValue('formfactor')][computePropValue('locale')][computePropValue('user.agent')];
      var idx = strongName.indexOf(':');
      if (idx != -1) {
        softPermutationId = parseInt(strongName.substring(idx + 1), 10);
        strongName = strongName.substring(0, idx);
      }
    }
     catch (e) {
    }
    envisionplacevalueblocks.__softPermutationId = softPermutationId;
    return computeUrlForResource(strongName + '.cache.js');
  }

  function loadExternalStylesheets(){
    if (!$wnd_0.__gwt_stylesLoaded) {
      $wnd_0.__gwt_stylesLoaded = {};
    }
    sendStats('loadExternalRefs', 'begin');
    sendStats('loadExternalRefs', 'end');
  }

  processMetas();
  envisionplacevalueblocks.__moduleBase = computeScriptBase();
  activeModules['envisionplacevalueblocks'].moduleBase = envisionplacevalueblocks.__moduleBase;
  var filename_0 = getCompiledCodeFilename();
  loadExternalStylesheets();
  sendStats('bootstrap', 'end');
  installScript(filename_0);
  return true;
}

envisionplacevalueblocks.succeeded = envisionplacevalueblocks();
