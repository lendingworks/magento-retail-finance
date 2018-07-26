/******/ (function(modules) { // webpackBootstrap
    /******/ 	// The module cache
    /******/ 	var installedModules = {};
    /******/
    /******/ 	// The require function
    /******/ 	function __webpack_require__(moduleId) {
        /******/
        /******/ 		// Check if module is in cache
        /******/ 		if(installedModules[moduleId]) {
            /******/ 			return installedModules[moduleId].exports;
            /******/ 		}
        /******/ 		// Create a new module (and put it into the cache)
        /******/ 		var module = installedModules[moduleId] = {
            /******/ 			i: moduleId,
            /******/ 			l: false,
            /******/ 			exports: {}
            /******/ 		};
        /******/
        /******/ 		// Execute the module function
        /******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
        /******/
        /******/ 		// Flag the module as loaded
        /******/ 		module.l = true;
        /******/
        /******/ 		// Return the exports of the module
        /******/ 		return module.exports;
        /******/ 	}
    /******/
    /******/
    /******/ 	// expose the modules object (__webpack_modules__)
    /******/ 	__webpack_require__.m = modules;
    /******/
    /******/ 	// expose the module cache
    /******/ 	__webpack_require__.c = installedModules;
    /******/
    /******/ 	// define getter function for harmony exports
    /******/ 	__webpack_require__.d = function(exports, name, getter) {
        /******/ 		if(!__webpack_require__.o(exports, name)) {
            /******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
            /******/ 		}
        /******/ 	};
    /******/
    /******/ 	// define __esModule on exports
    /******/ 	__webpack_require__.r = function(exports) {
        /******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
            /******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
            /******/ 		}
        /******/ 		Object.defineProperty(exports, '__esModule', { value: true });
        /******/ 	};
    /******/
    /******/ 	// create a fake namespace object
    /******/ 	// mode & 1: value is a module id, require it
    /******/ 	// mode & 2: merge all properties of value into the ns
    /******/ 	// mode & 4: return value when already ns object
    /******/ 	// mode & 8|1: behave like require
    /******/ 	__webpack_require__.t = function(value, mode) {
        /******/ 		if(mode & 1) value = __webpack_require__(value);
        /******/ 		if(mode & 8) return value;
        /******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
        /******/ 		var ns = Object.create(null);
        /******/ 		__webpack_require__.r(ns);
        /******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
        /******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
        /******/ 		return ns;
        /******/ 	};
    /******/
    /******/ 	// getDefaultExport function for compatibility with non-harmony modules
    /******/ 	__webpack_require__.n = function(module) {
        /******/ 		var getter = module && module.__esModule ?
            /******/ 			function getDefault() { return module['default']; } :
            /******/ 			function getModuleExports() { return module; };
        /******/ 		__webpack_require__.d(getter, 'a', getter);
        /******/ 		return getter;
        /******/ 	};
    /******/
    /******/ 	// Object.prototype.hasOwnProperty.call
    /******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
    /******/
    /******/ 	// __webpack_public_path__
    /******/ 	__webpack_require__.p = "https://integration.secure.lendingworks.co.uk/";
    /******/
    /******/
    /******/ 	// Load entry module and return exports
    /******/ 	return __webpack_require__(__webpack_require__.s = "./src/checkout/checkout.js");
    /******/ })
/************************************************************************/
/******/ ({

    /***/ "./src/checkout/checkout.js":
    /*!**********************************!*\
      !*** ./src/checkout/checkout.js ***!
      \**********************************/
    /*! no static exports found */
    /***/ (function(module, exports, __webpack_require__) {

        "use strict";
        /* eslint-disable no-restricted-globals */ /* eslint-disable no-alert */ /* global LendingWorksCheckout */(function(window,document){window.LendingWorksCheckout=function(token,sourcePage,completionHandler){var publicUrl="https://integration.secure.lendingworks.co.uk";function windowMessageListener(event){if(event.origin!==publicUrl){return;}// Run this when post messages
            if(event.data.event_id==='CLOSELWIFRAME'){if(event.data.data.status&&event.data.data.uuid){var getStatus=event.data.data.status;var getReference=event.data.data.reference;var getUuid=event.data.data.uuid;completionHandler(getUuid,getStatus,getReference);}else{completionHandler(null,null);}var oframe=document.getElementById('outerIframe');oframe.parentNode.removeChild(oframe);var iframe=document.getElementById('innerIframe');iframe.parentNode.removeChild(iframe);document.body.classList.remove('lw-modal-visible');}// Set parent localstroage from iframe
            if(event.data.event_id==='SETLOCALSTORAGES'){if(event.data.data.lwalertstatus==='applied'){var getAlertStatus=event.data.data.lwalertstatus;window.localStorage.setItem('lwalertstatus',getAlertStatus);}else if(event.data.data.lwalertstatus==='completed'){var _getAlertStatus=event.data.data.lwalertstatus;window.localStorage.setItem('lwalertstatus',_getAlertStatus);}}}return function(){// Create the iframe
            var iframe=document.createElement('iframe');iframe.id='innerIframe';iframe.src="".concat(publicUrl,"/checkout/?token=").concat(token,"&url=").concat(sourcePage);iframe.style.background='#fff';iframe.style.zIndex='99999999';iframe.style.display='block';iframe.style.position='absolute';iframe.style.borderWidth='0';var screenWidth=document.documentElement.clientWidth;var screenHeight=document.documentElement.clientHeight;if(screenWidth<450){iframe.style.top='15px';iframe.style.left='15px';iframe.style.width="".concat(screenWidth-30,"px");iframe.style.height="".concat(screenHeight-60,"px");}else{iframe.style.top='30px';var tleft=(screenWidth-400)/2;iframe.style.left="".concat(tleft,"px");iframe.style.width='450px';iframe.style.height="".concat(screenHeight-100,"px");}var outIframe=document.createElement('iframe');outIframe.id='outerIframe';outIframe.style.background='#000';outIframe.style.opacity='0.4';outIframe.style.visibility='visible';outIframe.style.top='0px';outIframe.style.left='0px';outIframe.style.display='block';outIframe.style.height='100vh';outIframe.style.width='100vw';outIframe.style.margin='0px';outIframe.style.padding='0px';outIframe.style.position='fixed';outIframe.style.border='0px none transparent';outIframe.style.cursor='pointer';window.scrollTo(0,0);var lwiframevisible=document.createElement('style');lwiframevisible.type='text/css';lwiframevisible.innerHTML='.lw-modal-visible {  overflow: hidden;}';document.getElementsByTagName('head')[0].appendChild(lwiframevisible);document.body.classList.add('lw-modal-visible');document.body.appendChild(outIframe);document.body.appendChild(iframe);// add localstorage retail sites
            window.localStorage.setItem('lwalertstatus','start');document.getElementById('outerIframe').contentDocument.addEventListener('click',function(){function ClosePopupOverlay(){// Send a message to the child iframe to clear localStorage
                var iframeWin=document.getElementById('innerIframe');// periodical message sender
                var message='CLEARLOCALSTORAGE';iframeWin.contentWindow.postMessage(message,publicUrl);// close overlay
                setTimeout(function(){var oframeclose=document.getElementById('outerIframe');oframeclose.parentNode.removeChild(oframeclose);var iframclose=document.getElementById('innerIframe');iframclose.parentNode.removeChild(iframclose);document.body.classList.remove('lw-modal-visible');document.getElementsByTagName('head')[0].removeChild(lwiframevisible);completionHandler(null,'aborted');},1000);}var localStorageStatus=window.localStorage.getItem('lwalertstatus');var alertMessages;// user should not see the browser "are you sure?" warning when closes the flow on this page by clicking outside the popup,
                if(localStorageStatus==='completed'){ClosePopupOverlay();}else{if(localStorageStatus==='applied'){alertMessages="Are you sure you want to leave? We'll email you a link so you can finish your order later.";}else{alertMessages="Are you sure you want to leave? Unfinished applications won't be saved.";}if(window.confirm(alertMessages)){ClosePopupOverlay();}}},false);window.addEventListener('message',windowMessageListener,false);};};window.addEventListener('load',function(){var createCompletionHandler=function createCompletionHandler(statusField,idField,referenceField){return function(id,status,reference){// Add form elements that record this.
            statusField.setAttribute('value',status);idField.setAttribute('value',id);referenceField.setAttribute('value',reference);};};var checkoutButtons=document.getElementsByClassName('lw-btn');Array.from(checkoutButtons).forEach(function(element,index,array){var btn=document.createElement('BUTTON');var t=document.createTextNode('Checkout');var thisId="lw-checkout-button-".concat(index);btn.setAttribute('id',thisId);btn.setAttribute('class','lw-checkout-button');btn.style.fontSize='15px';btn.style.backgroundColor='#007bff';btn.style.borderColor='#007bff';btn.style.color='#fff';btn.style.padding='10px 15px';btn.style.cursor='pointer';var thisToken=array[index].dataset.token;var sourcePage=array[index].parentNode.action;btn.appendChild(t);// create input field
            var status=document.createElement('INPUT');status.setAttribute('name','lendingWorksOrderStatus');status.setAttribute('id',"LWSTATUS-".concat(index));status.setAttribute('type','hidden');var id=document.createElement('INPUT');id.setAttribute('name','lendingWorksOrderId');id.setAttribute('id',"LWORDERID-".concat(index));id.setAttribute('type','hidden');var loanReference=document.createElement('INPUT');loanReference.setAttribute('name','lendingWorksLoanReference');loanReference.setAttribute('id',"LWREFERENCE-".concat(index));loanReference.setAttribute('type','hidden');var e=element;e.parentNode.appendChild(status);e.parentNode.appendChild(id);e.parentNode.appendChild(loanReference);e.replaceWith(btn,element);e.remove(element);document.getElementById(thisId).addEventListener('click',function(event){// Prevent the form from submitting with the default action.
                event.preventDefault();var checkoutHandler=LendingWorksCheckout(thisToken,sourcePage,createCompletionHandler(status,id,loanReference));checkoutHandler();});});},false);window.addEventListener('resize',function(){var iframeclose=document.getElementById('innerIframe');var screenWidth=document.documentElement.clientWidth;if(screenWidth<450){iframeclose.style.top='15px';iframeclose.style.left='15px';iframeclose.style.width="".concat(screenWidth-30,"px");}else{iframeclose.style.top='30px';var tleft=(screenWidth-400)/2;iframeclose.style.left="".concat(tleft,"px");iframeclose.style.width='450px';}},false);})(window,document);

        /***/ })

    /******/ });
//# sourceMappingURL=checkout.js.map