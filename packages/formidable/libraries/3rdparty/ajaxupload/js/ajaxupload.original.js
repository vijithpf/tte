/**
 * jQuery Real Ajax Uploader 3.0
 * http://www.albanx.com/
 *
 * Copyright 2010-2013, Alban Xhaferllari
 *
 * Date: 03-11-2013
 */


//==================================================================CLASS DEFINITION IN JS========================================================\\
/* Simple JavaScript Inheritance
 * By John Resig http://ejohn.org/
 * MIT Licensed.
 */
// Inspired by base2 and Prototype
(function(){
  var initializing = false, fnTest = /xyz/.test(function(){xyz;}) ? /\b_super\b/ : /.*/;
  this.Class = function(){};
  Class.extend = function(prop) {
    var _super = this.prototype;
    initializing = true;
    var prototype = new this();
    initializing = false;
   
    for (var name in prop) {
      prototype[name] = typeof prop[name] == "function" &&
        typeof _super[name] == "function" && fnTest.test(prop[name]) ?
        (function(name, fn){
          return function() {
            var tmp 	= this._super;
            this._super = _super[name];
            var ret 	= fn.apply(this, arguments);        
            this._super = tmp;
           
            return ret;
          };
        })(name, prop[name]) :
        prop[name];
    }
   
    function Class() {
      if ( !initializing && this.init )
        this.init.apply(this, arguments);
    }
    Class.prototype = prototype;
    Class.prototype.constructor = Class;
    Class.extend = arguments.callee;
    return Class;
  };
})();
//==========================================================================================================================================\\



/**
 * jQuery Real Ajax Uploader 3.0
 * http://www.albanx.com/
 *
 * Copyright 2010-2013, Alban Xhaferllari
 *
 * Date: 03-11-2013
 */

(function($, undefined){
	'use strict'; 
    
	//=============================================================Simple i18 system============================================================\\
	//String used in the uploader : STRING: POSITION
	var strings = {
			'Add files':0,
			'Start upload':1,
			'Remove all':2,
			'Close':3,
			'Select Files':4,
			'Preview':5,
			'Remove file':6,
			'Bytes':7,
			'KB':8,
			'MB':9,
			'GB':10,
			'Upload aborted':11,
			'Upload all files':12,
			'Select Files or Drag&Drop Files':13,
			'File uploaded 100%':14,
			'Max files number reached':15,
			'Extension not allowed':16,
			'File size now allowed':17
	};
	
	//translations for STRINGS in array for each language
	var I18N = {
			'it_IT':[
				'Aggiungi file',
				'Inizia caricamento',
				'Rimuvi tutti',
				'Chiudi',
				'Seleziona',
				'Anteprima',
				'Rimuovi file',
				'Bytes',
				'KB',
				'MB',
				'GB',
				'Interroto',
				'Carica tutto',
				'Seleziona o Trascina qui i file',
				'File caricato 100%',
				'Numero massimo di file superato',
				'Estensione file non permessa',
				'Dimensione file non permessa'
			],
			'sq_AL':[
				'Shto file',
				'Fillo karikimin',
				'Hiqi te gjithë',
				'Mbyll',
				'Zgjith filet',
				'Miniaturë',
				'Hiqe file-in',
				'Bytes',
				'KB',
				'MB',
				'GB',
				'Karikimi u ndërpre',
				'Kariko të gjithë',
				'Zgjith ose Zvarrit dokumentat këtu',
				'File u karikua 100%'
			],
			'nl_NL':[
			    'Bestanden toevoegen',
			    'Start uploaden',
			    'Verwijder alles',
			    'Sluiten',
			    'Selecteer bestanden',
			    'Voorbeeld',
			    'Verwijder bestand',
			    'Bytes',
			    'KB',
			    'MB',
			    'GB',
			    'Upload afgebroken',
			    'Upload alle bestanden',
			    'Selecteer bestanden of  Drag&Drop bestanden',
			    'Bestand geüpload 100%'
			   ],
			   'de_DE':[
                'Dateien hinzufügen',
                'Hochladen',
                'Alle entfernen',
                'Schliessen',
                'Dateien wählen',
                'Vorschau',
                'Datei entfernen',
                'Bytes',
                'KB',
                'MB',
                'GB',
                'Upload abgebrochen',
                'Alle hochgeladen',
                'Wählen Sie Dateien oder fügen Sie sie mit Drag & Drop hinzu.',
                'Upload 100%'
          ],
		   'fr_FR':[
               'Ajouter',
               'Envoyer',
               'Tout supprimer',
               'Fermer',
               'Parcourir',
               'Visualiser',
               'Supprimer fichier',
               'Bytes',
               'Ko',
               'Mo',
               'Go',
               'Envoi annulé',
               'Tout envoyer',
               'Parcourir ou Glisser/Déposer',
               'Fichier envoyé 100%']
	};
	
	//current loaded translation
	var AX_I18N = {};
	function load18(lang){
		AX_I18N = I18N[lang];
	}
	
	//the translation function
	function _(s) {
		return AX_I18N ? (AX_I18N[strings[s]] || s) : s;
	}
	//===========================================================================================================================================\\
	
	
	
	//===============================================File object for uploader=====================================================================\\
	//contains information about file, the upload status of file and some callback functions
	var fileObject = Class.extend({
		init: function(file, name, size, ext, AU)
		{
			//File properties
			this.file 		= file; 			//real dom file object
			this.status 	= 0; 				//status -1 error, 0 idle 1 done, 2 uploading
			this.name		= name; 			//name of file
			this.size		= size; 			//size of file
			this.xhr		= null; 			//xmlhttprequest object or form 
			this.info		= null; 			//info about upload status
			this.ext 		= ext; 				//file extension
			this.pos		= AU.files.length; 	//position of file in the array needed for file remove
			this.AU 		= AU; 				//AjaxUploader object
			this.settings 	= AU.settings;
			this.exifData	= null;
			//temp variables
			this.currentByte= 0; 	//current uploaded byte
			
			this.afterInit();
		},
		afterInit: function()
		{
			//visual part
			this.renderHtml();
	    	
	    	//bind events
	    	this.bindEvents();
	    	
	    	//create the small preview
	    	this.doPreview();
	
			if(this.settings.hideUploadForm && this.AU.form!==null && this.AU.form!==undefined)
			{
				this.uploadButton.hide();
			}
			
			var AU = this.AU;
	    	//if ajax upload is not supported then add a form around the file to upload it
	    	if(AU.hasHtml4)
	    	{
	    		var params = AU.getParams(this.name, 0, false);   		
	    		//create the upload form
	    		var form = $('<form action="'+this.settings.url+'" method="post" target="ax-main-frame" encType="multipart/form-data" />').hide().appendTo(this.li);
	    		form.append(this.file);	
	    		//append to the form eventually the changed name of the uploaded file
	        	form.append('<input type="hidden" value="'+this.name+'" name="ax-file-name" />');//input for re-name of file
				for(var i=0; i<params.length;i++)
				{
					var d = params[i].split('=');
					form.append('<input type="hidden" value="'+d[1]+'" name="'+d[0]+'" />');
				}
				
	        	this.xhr = form;
	    	}
		},
		renderHtml: function()
		{
			var settings = this.settings;
			//create visual part
			var size			= this.AU.formatSize(this.size);
		    this.li				= $('<li />').appendTo(this.AU.fileList).attr('title', name);//li element container
		    if(settings.bootstrap)
		    {
		    	this.li = $('<a />').appendTo(this.li);
		    }
		    
		    this.prevContainer	= $('<a class="ax-prev-container" />').appendTo( this.li );//preview container
		    this.details		= $('<div class="ax-details" />').appendTo( this.li ); //div containing details of files
		    this.progressInfo	= $('<div class="ax-progress" />').appendTo( this.li ); //progress infomation container
		    this.buttons 		= $('<div class="ax-toolbar" />').appendTo( this.li ); //button container

		    this.prevImage		= $('<img class="ax-preview" src="" alt="' + _('Preview') + '" />').appendTo( this.prevContainer ); //preview image
		    this.nameContainer	= $('<div class="ax-file-name">'+this.name+'</div>').appendTo( this.details ); //name container
		    this.sizeContainer	= $('<div class="ax-file-size">'+size+'</div>').appendTo( this.details ); //size container
		    this.progressBar	= $('<div class="ax-progress-bar" />').appendTo( this.progressInfo ); //animated progress bar
		    this.progressPer	= $('<div class="ax-progress-info">0%</div>').appendTo( this.progressInfo ); //progress percentual
	    	this.uploadButton 	= $('<a title="' + _('Start upload') + '" class="ax-upload ax-button" />').appendTo( this.buttons ).append('<span class="ax-upload-icon ax-icon"></span>');//upload button
	    	this.removeButton 	= $('<a title="Remove file" class="ax-remove ax-button" />').appendTo( this.buttons ).append('<span class="ax-clear-icon ax-icon"></span>'); //remove button
	    	
	    	if(settings.bootstrap)
	    	{
	    		this.li.addClass('media thumbnail label-info');
	    		this.prevContainer.addClass('pull-left');
	    		this.prevImage.addClass('img-rounded media-object');
	    		
	    		this.details.addClass('label label-info').css({ 'border-bottom-left-radius':0});

	    		this.progressInfo.addClass('progress progress-striped active').css({'margin-bottom':0});
	    		this.progressBar.addClass('bar');
	    		
	    		this.buttons.css({ 'border-top-left-radius':0, 'border-top-right-radius':0});
	    		this.uploadButton.addClass('btn btn-success btn-small').find('span').addClass('icon-play');
	    		this.removeButton.addClass('btn btn-danger btn-small').find('span').addClass('icon-minus-sign');
	    	}
		},

		//====================== Bind action events, upload, remove, preview =======================\\
		bindEvents: function()
		{
		   	//bind start upload
			this.uploadButton.bind('click', this, function(e){
				if(e.data.AU.settings.enable)
				{
					if(e.data.status!=2)//start upload
					{
						e.data.startUpload();
					}
					else//if is uploading then stop on reclick
					{
						e.data.stopUpload();
					}
				}
			});
			   
			//bind remove file
			this.removeButton.bind('click', this, function(e){ 
				if(e.data.AU.settings.enable) e.data.AU.removeFile(e.data.pos);	
			});
			
		    if(this.settings.editFilename)
		    {
		    	//on double click bind the edit file name
		    	this.nameContainer.bind('dblclick', this, function(e){
		    		if(e.data.AU.settings.enable)
		    		{
				    	e.stopPropagation();
				    	var file_name = e.data.name;
				    	var file_ext = e.data.ext;
				    	//get file name without extension
				    	var file_name_no_ext = file_name.replace('.'+file_ext, '');
				    	$(this).html('<input type="text" value="'+file_name_no_ext+'" />.'+file_ext);
		    		}
			    	
			    }).bind('blur focusout', this, function(e){
		    		e.stopPropagation();
		    		var new_fn = $(this).children('input').val();
		    		if( typeof(new_fn) != 'undefined' )
		    		{
		    			var cleanString = new_fn.replace(/[|&;$%@"<>()+,]/g, '');//remove bad filename chars
		    			var final_fn = cleanString+'.'+e.data.ext;
		    			$(this).html(final_fn);
		    			e.data.name = final_fn;
		    			if(!e.data.AU.hasAjaxUpload)//on form upload also rename input hidden input
		    			{
		    				//change the name of hidden input in the uploader form
		    				e.data.xhr.children('input[name="ax-file-name"]').val(final_fn);
		    			}
		    		}
			    });
		    }
		},
		
		
		//=================================== function that creates the preview of images ===============================\\
		doPreview: function()
		{
			//if filereader html5 preview is supported that make the preview on the fly
	   		if (this.AU.settings.previews && this.AU.hasAjaxUpload && this.file.type.match(/image.*/) && (this.ext=='jpg' || this.ext=='gif' || this.ext=='png') && typeof (FileReader) !== "undefined")
		    {
	   			var name = this.name;
	   			var me = this;
	   			//remove the background image of the preview container
	   			this.prevContainer.css('background','none');
	   			
	   			//the image that will contain the preview
	   			var img = this.prevImage;
	   			
	   			//file reader object for reading and loading image
			    var reader = new FileReader();  
			    reader.onload =function(e) {
			    	//set the image cursort to pointer for indicating
			    	img.css('cursor','pointer').attr('src', e.target.result).click(function(){
			    		
			    		//create a image loader for getting image size
			   			var imgloader = new Image();
			   			imgloader.onload = function()
			   			{
			   				//resize image to fit the user window size
			   			    var ratio = Math.min( $(window).width() / this.width, ($(window).height()-100) / this.height);
			   			    var newWidth = (ratio<1)?this.width * ratio:this.width;
			   			    var newHeight = (ratio<1)?this.height * ratio:this.height;

			   			    var axtop = ($(window).scrollTop()-20+($(window).height()-newHeight)/2);
			   			    var axleft= ($(window).width()-newWidth)/2;
			   			    
			   			    //set preview box position and dimension accordin to screen
			   			    var axbox = $('#ax-box').css({ top:  axtop, height:newHeight, width:newWidth, left: axleft});
			   			    
			   			    //set the preview image
			   			    axbox.children('img').attr({ width: newWidth, height:newHeight, src:e.target.result });
			   			    
			   			    //set the name of the file
			   			    $('#ax-box-fn').find('span').html(name + ' ('+me.AU.formatSize(me.size)+')');
			   			    
			   			    //then show in the preview
			   			    axbox.fadeIn(500);			
			   			    
			   			    //expand blocking overlay
				    		$('#ax-box-shadow').css('height', $(document).height()).show();
			   			};
			   			//load the image
			   			imgloader.src = e.target.result;
			   			
						$('#ax-box-shadow').css('z-index', 10000);
						$('#ax-box').css('z-index', 10001);
			    	});
			    };  
			    
			    //read file from fs
			    reader.readAsDataURL(this.file); 
		    }
		    else
		    {
		    	//if not supported or is not image file that load the icon of file type
		    	this.prevContainer.addClass('ax-filetype-'+this.ext).children('img:first').remove();
		    }
		},
		
		askUser: function(callback, msg)
		{
			if(this.askDiv) this.askDiv.remove();
		    this.askDiv		= $('<div class="ax-ask-div"></div>').appendTo(this.li); // div with more action and infos
		    var askDivInner	= $('<div class="ax-ask-inner"><div class="ax-ask-quest">'+msg+'</div> </div>').appendTo(this.askDiv); // div with more action and infos
	    	var askYes 		= $('<a title="Yes" class="ax-button ax-ask-yes"><span class="ax-upload-icon ax-icon"></span> <span>Yes</span></a>').appendTo(askDivInner);
	    	var askNo 		= $('<a title="No" class="ax-button ax-ask-no"><span class="ax-clear-icon ax-icon"></span> <span>No</span></a>').appendTo(askDivInner);
	    	
	    	if(this.settings.bootstrap)
	    	{
	    		this.askDiv.addClass('alert');
	    		askYes.addClass('btn btn-success btn-small').find('.ax-icon').addClass('icon-ok');
	    		askNo.addClass('btn btn-danger btn-small').find('.ax-icon').addClass('icon-remove');
	    	}
	    	
	    	askYes.on('click', this, function(e){
	    		callback.call(e.data);
	    		e.data.askDiv.remove();
	    		e.data.askDiv = null;
	    	});
	    	
	    	askNo.on('click', this, function(e){
	    		e.data.askDiv.remove();
	    		e.data.askDiv = null;
	    	});
		},
    
	    //Check if a file exits before upload, and ask user if want to ovveride or stop upload
	    //params: fileobjct, and callback to call (normally upload start)
	    checkFileExists: function(callback)
	    {
	    	var fileobj = this;
			if(this.settings.checkFileExists)
			{
		    	var params = this.AU.getParams(fileobj.name, fileobj.size, false);
		    	params.push('ax-check-file=1');
		    	$.post(this.settings.url, params.join('&'), function(msg){
		    		if(msg=='yes')
		    		{
		    			fileobj.askUser(callback, _('File exits on server. Override?'));
		    		}
		    		else
		    		{
		    			callback.call(fileobj);
		    		}
		    	});
			}
			else
			{
				callback.call(fileobj);
			}
	    },
	    
		/**
		 * Start upload method
		 */
		startUpload: function()
		{
			//check if the before upload returns true, from user validation event
			var valid = this.settings.beforeUpload.call(this, this.name, this.file);
			if(valid)
			{
				this.status = 3;//check status
				this.checkFileExists(function(){
					//upload code as callback of check file exits
					this.progressBar.css('width','0%');
					this.progressPer.html('0%');
					this.uploadButton.addClass('ax-abort');
					this.status = 2;//uploading status
					if(this.AU.hasAjaxUpload)//html5 upload
					{
						this.uploadAjax();
					}
					else if(this.AU.hasFlash) //flash upload
					{
						
						if(!this.AU.uploading)
						{
							this.AU.uploading = true;
							this.AU.flashObj.uploadFile(this.pos);
						}
					}
					else //standard html4 upload
					{
						this.uploadStandard();
					}
					
				});
			}
			else
			{
				this.status = -1;//error validation
				this.onError('File validation failed');
			}
			return valid;
		},
		
		/**
		 * Main upload ajax html5 method, uses xmlhttprequest object for uploading file
		 * Runs in recrusive mode for uploading files by chunk
		 */
		uploadAjax: function()
		{
			var settings 	= this.settings;
			var file		= this.file;
	    	var currentByte	= this.currentByte;
	    	var name		= this.name;
	    	var size		= this.size;
	    	var chunkSize	= settings.chunkSize;	//chunk size
			var endByte		= chunkSize + currentByte;
			var isLast		= (size - endByte <= 0);
	    	var chunk		= file;
	    	var chunkNum	= chunkSize!=0 ? endByte / chunkSize: 1;
	    	this.xhr 		= new XMLHttpRequest();//prepare xhr for upload
	    	
	    	if(chunkSize == 0)//no divide
	    	{
	    		chunk	= file;
	    		isLast	= true;
	    	}
	    	else if(file.slice) // old slice, there are two version of the same
	    	{
	    		chunk	= file.slice(currentByte, endByte);
	    	}
	    	else if(file.mozSlice) // moz slice
	    	{
	    		chunk	= file.mozSlice(currentByte, endByte);
	    	}
	    	else if(file.webkitSlice) //webkit slice
	    	{
	    		chunk	= file.webkitSlice(currentByte, endByte);
	    	}
	    	else//no slice
	    	{
	    		chunk	= file;
	    		isLast	= true;
	    	}
	    	
	    	var me = this;
	    	//abort event, (nothing to do for the moment)
	    	this.xhr.upload.addEventListener('abort', function(e){
	    		me.AU.slots--;
	    	}, false); 
	    	
	    	//progress function, with ajax upload progress can be monitored
	    	this.xhr.upload.addEventListener('progress', function(e)
			{
				if (e.lengthComputable) 
				{
					var perc = Math.round((e.loaded + chunkNum * chunkSize - chunkSize) * 100 / size);
					me.onProgress(perc);
				}  
			}, false); 
	    	    	
	    	this.xhr.upload.addEventListener('error', function(e){
	    		me.onError(this.responseText);
	    	}, false);  
	    	
	    	this.xhr.onreadystatechange=function()
			{
				if(this.readyState == 4 && this.status == 200)
				{
					try
					{
						var ret	= JSON.parse( this.responseText );
						//get the name returned by the server (it renames eventually)
						if(currentByte == 0)
						{
							me.name	= ret.name;
							me.nameContainer.html(ret.name);
						}
						
						if(parseInt(ret.status) == -1)
						{
							throw ret.info;
						}
						else if(isLast)
						{
							//exec finish event of the file
							me.onFinishUpload(ret.name, ret.size, ret.status, ret.info);
						}
						else
						{
							me.currentByte = endByte;
							me.uploadAjax();
						}
					}
					catch(err)
					{
						me.onError(err);
					}
				}
			};
		
			var params = this.AU.getParams(name, size, !this.AU.useFormData);
			params.push('ax-start-byte='+ currentByte);
			params.push('ax-last-chunk='+ isLast);

			if(this.AU.useFormData) //firefox 5 formdata does not work correctly
			{
				var data = new FormData();
				data.append('ax_file_input', chunk);
				for(var i=0; i<params.length; i++)
				{
					var d = params[i].split('=');
					data.append(d[0], d[1] );
				}
				this.xhr.open('POST', settings.url, settings.async);
				this.xhr.send(data);
			}
			else//else we use a old trick upload with php::/input ajax, FF3.6+, Chrome, Safari
			{
				var c =  settings.url.indexOf('?')==-1 ?'?':'&';			
				this.xhr.open('POST', settings.url+c+params.join('&'), settings.async);
				this.xhr.setRequestHeader('Cache-Control', 'no-cache');
				this.xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');//header
				this.xhr.setRequestHeader('Content-Type', 'application/octet-stream');//generic stream header
				this.xhr.send(chunk);//send peice of file
			}
		},
		
		/**
		 * starndard upload function for normal browsers
		 */
		uploadStandard: function()
		{
			//just fake upload in standard forms upload
			this.progressBar.css('width','50%');
			this.progressPer.html('50%');
			$('#ax-main-frame').unbind('load').bind('load', this, function(e){
				//get iframe content
				var frameDoc = null;
				if ( this.contentDocument ) 
				{ // FF
					frameDoc = this.contentDocument;
				}
				else if ( this.contentWindow ) 
				{ // IE
					frameDoc = this.contentWindow.document;
				}
	    		
				//hope is a json, from which we get file information
				try{
					var ret	= $.parseJSON(frameDoc.body.innerHTML);
		    		//set progress to 100%
		    		e.data.onProgress(100);
		    		
		    		//get file info
		    		e.data.onFinishUpload(ret.name, ret.size, ret.status, ret.info);
				}catch(err){
					e.data.onError(frameDoc.body.innerHTML);
				}
	   		
	    		//if upload all flag is set then try to upload the next file
	    		if(e.data.AU.upload_all && e.data.AU.files[e.data.pos+1]!==undefined)
	    		{
	    			e.data.AU.files[e.data.pos+1].startUpload();
	    		}
			});
			
			//submit the form of the standard upload
			this.xhr.submit();
		},
	    /**
	     * Stop upload function. it reset visual information and if the upload is xhr it calls the abort
	     * method, or if upload is, iframe standard based it stops the iframe request
	     */
		stopUpload: function()
		{
			if(this.AU.hasAjaxUpload)
			{
				if(this.xhr!==null)//if is current uploading 
				{
					this.xhr.abort();	//abort xmlhttprequest request
					this.xhr 	= null;//remove the xhr / form object
				}
			}
			else if(this.AU.hasFlash)
			{
				this.AU.flashObj.stopUpload(this.pos);//call flash stop upload method
			}
			else
			{
				var iframe	= document.getElementById('ax-main-frame');
				//stop iframe from uploading
				try{
					iframe.contentWindow.document.execCommand('Stop');
				}
				catch(ex){
					iframe.contentWindow.stop();
				}
			}
			
			this.uploadButton.removeClass('ax-abort');//show upload button
			this.currentByte = 0; //reset current byte
			this.status = 0;//set status to idle
			this.progressBar.css('width', 0);// reset progress bar
			this.progressPer.html( _('Upload aborted') );//print abort info
		},
		/**
		 * Runs on error
		 * @param err error return from the server
		 */
		onError : function(err)
		{
			this.AU.slots--;
	    	this.currentByte = 0;
	    	this.status	= -1;
	    	this.info	= err;
	    	this.progressPer.html(err);
	    	this.progressBar.css('width','0%');
	    	this.uploadButton.removeClass('ax-abort');

	    	//trigger the error event
			this.settings.error.call(this, err, this.name);
			
			if(this.settings.removeOnError)
	    	{
	    		this.AU.removeFile(this.pos);
	    	}
		},
		onFinishUpload : function(name, size, status, info)
		{
			this.AU.slots--;
	    	this.name	= name;
	    	this.status	= parseInt(status);
	    	this.info	= info;


	    	//in html5 file and flash api we read size from browser, in standard upload we get it from the server
	    	if(!this.AU.hasAjaxUpload && !this.AU.hasFlash)
	    	{
	    		var size = this.AU.formatSize(this.size);
	    		this.sizeContainer.html(size);
	    	}
	    	
	    	this.currentByte = 0;
	    	this.nameContainer.html(name);//get new name of file from server
	    	this.li.attr('title', name);
	    	this.onProgress(100);
	    	this.uploadButton.removeClass('ax-abort');//remove abort button
	    	this.progressBar.width(0);
	    	this.progressPer.html(_('File uploaded 100%')); 
	    	this.settings.success.call(this, name);//call success method
	    	
	    	//if all files had been uploaded then exec finish event
	    	var runFinish = true;
	    	for(var i = 0; i<this.AU.files.length; i++)
	    	{
	    		//so if we have any file still at idle do not run finish event
	    		if(this.AU.files[i].status!=1 && this.AU.files[i].status!=-1) runFinish = false;
	    	}

	    	//if upload all was pressed, try to upload next files
	    	if(this.AU.upload_all)
	    	{
	    		this.AU.uploadAll();
	    	}
	    	
	    	if(runFinish)
	    	{
	    		this.AU.finish();
	    	}
	    	
	    	
	    	if(this.settings.removeOnSuccess)
	    	{
	    		this.AU.removeFile(this.pos);
	    	}
		},
		/**
		 * Function that is trigger on the progress event of the upload
		 * updates progress bar and percentual
		 */
		onProgress: function(p){
			this.progressBar.css('width',p+'%');
			this.progressPer.html(p+'%');
		}
	});
 	
	//3.0 Premium feature: exif info
	fileObject = fileObject.extend({
		renderHtml: function()
		{
			this._super();
			if(this.AU.hasAjaxUpload && this.ext.toLowerCase()=='jpg' && $.fileExif)//TODO only for jpg now
			{
		    	this.infoButton = $('<a title="File Exif data" class="ax-info ax-button" />').appendTo( this.buttons ).append('<span class="ax-info-icon ax-icon"></span>');
		    	if(this.settings.bootstrap)
		    	{
		    		this.infoButton.addClass('btn btn-primary btn-small').find('span').addClass('icon-info-sign');
		    	}
			}
		},
		afterInit: function()
		{
			this._super();
			var AU = this.AU;
			if(AU.hasAjaxUpload && $.fileExif && this.ext.toLowerCase()=='jpg')//extrac axif only on html5 api browsers
	    	{
	    		var me = this;
	    		$.fileExif(this.file, function(data){
	    			me.exifData = data;
	    		});
	    	}
		},
		bindEvents: function()
		{
		    this._super();
		    if(this.AU.hasAjaxUpload && $.fileExif && this.ext.toLowerCase()=='jpg')
		    {
			    this.infoButton.bind('click', this, function(e){
			    	e.data.settings.fileInfo.call(e.data, e.data.exifData);
			    });
		    }
		}
	});
	
	
	//allow delete file
	fileObject = fileObject.extend({
		renderHtml: function()
		{
			this._super();
			if(this.settings.allowDelete)
			{
				this.deleteButt = $('<a title="Delete the file from server" class="ax-delete ax-button ax-disabled" />').appendTo( this.buttons ).append('<span class="ax-delete-icon ax-icon"></span>');
		    	if(this.settings.bootstrap)
		    	{
		    		this.deleteButt.addClass('btn btn-warning btn-small').find('span').addClass('icon-remove');
		    	}
			}
		},
		bindEvents: function()
		{
		    this._super();
		    if(this.settings.allowDelete)
		    {
			    this.deleteButt.bind('click', this, function(e){
			    	var ofile = e.data;
			    	if(ofile.status==1 && !$(this).hasClass('ax-disabled')) //we can delete file only if has been uploaded
			    	{
				    	ofile.askUser(function(){
				    		ofile.deleteFile();
				    	}, _('Delete uploaded file?'));
			    	}
			    });
		    }
		},
		deleteFile: function()
		{
			if(this.settings.allowDelete)
			{
		    	var params = this.AU.getParams(this.name, this.size, false);
		    	params.push('ax-delete-file=1');
		    	$.post(this.settings.url, params.join('&'));
		    	this.status = 0;//reset status as file not uploaded
		    	this.deleteButt.addClass('ax-disabled');
			}
		},
		onFinishUpload : function(name, size, status, info)
		{
			this._super(name, size, status, info);
			if(this.status==1 && this.settings.allowDelete)
			{
				this.deleteButt.removeClass('ax-disabled');
			}
		}
	});
	
	
	/**
	 * Ajax Uploader class
	 */
	var AjaxUploader = function($this, settings)
	{
		//support variables
		this.hasFlash   	= false;
		this.hasAjaxUpload 	= false;
		this.useFormData 	= false;
		this.hasHtml4 		= true;
		
		this.settings = this.preCheckSettings(settings);
		this._init($this);
		this.checkUploadSupport();//check upload support
		
		//properties
		this.container 	= $this; 		// main container
		this.files 		= [];			// array with the fileObjects
		this.slots 		= 0;			// trace slots (parallel uploads), for limiting
		
		this.form 		= null; 		//if form integration
		this.form_submit_event = null;	
		this.flashObj	= null;
		
		//runtime vars
		this.upload_all	= false;
		this.uploading	= false;
		
		//render html
		this.renderHtml();
	    
	    //run the init call back
		settings.onInit.call(this);
	    
		//bind click mouse event and other events
	    this.bindEvents();
	};

	AjaxUploader.prototype = 
	{		
		preCheckSettings: function(settings)
		{
			settings.allowDelete 		= settings.allowDelete || false; 
			settings.checkFileExists 	= settings.checkFileExists || false;
			settings.allowExt 			= $.map(settings.allowExt, function(n, i){ return n.toLowerCase();  });
			
			//=============IOS7 is bugged on multiple upload of video files
			var is_bugged_ios7 =  navigator.userAgent.indexOf(" OS 7_") !== -1;
			if(is_bugged_ios7) settings.maxFiles = 1;
			//==================================================
			
			if(settings.language == 'auto')
			{
				var language = window.navigator.userLanguage || window.navigator.language;
				settings.language = language.replace('-', '_');
			}
			
			return settings;
		},
		_init: function($this)
		{
			var settings = this.settings;
			//load language
			load18(settings.language);
			
			$this.addClass('ax-uploader').data('author','http://www.albanx.com/');
			
			//create a iframe for standard uploads
			if($('#ax-main-frame').length==0) 	$('<iframe name="ax-main-frame" id="ax-main-frame" />').hide().appendTo('body');
			
			//lightbox preview for images
			if($('#ax-box').length==0)			$('<div id="ax-box"><div id="ax-box-fn"><span></span></div><img /><a id="ax-box-close" title="'+_('Close')+'"></a></div>').appendTo('body');//preview box
			if($('#ax-box-shadow').length==0)	$('<div id="ax-box-shadow"/>').appendTo('body');//preview shadow, overlay

			    $('#ax-box-close, #ax-box-shadow').click(function(e){
    			e.preventDefault();
    			$('#ax-box').fadeOut(500);
    			$('#ax-box-shadow').hide();
    		});
			    
		    if(settings.bootstrap)
		    {
		    	$('#ax-box-close').addClass('btn btn-danger').html('<span class="ax-clear-icon ax-icon icon-remove-sign"></span>');
		    }
		    
		    //generate an id if it has no one, so to point the uploader from flash
		    var unique_id = 'AX_'+Math.floor(Math.random()*100001); 
		    while($('#'+unique_id).length>0)
		    {
		    	unique_id = 'AX_'+Math.floor(Math.random()*100001); 
		    }  			     
		    $this.attr('id', $this.attr('id') ? $this.attr('id') : unique_id ); 
		},

		//check upload support
		checkUploadSupport: function()
		{
			//--------------Test if support pure ajax upload and create browse file input-------
			var axtest 			= document.createElement('input');
			axtest.type 		= 'file';
			this.hasAjaxUpload 	= ('multiple' in axtest &&  typeof File != "undefined" &&  typeof (new XMLHttpRequest()).upload != "undefined" );
			this.hasFlash 		= false;
			axtest 				= null; //avoid memory leak IE
			//----------------------------------------------------------------------------------
			
			//this.hasAjaxUpload = false;
			
			//--------safari<5.1.7 is bugged in file api so we force using flash upload---------
			var is_bugged_safari =  /Safari/.test(navigator.userAgent) && /Apple Computer/.test(navigator.vendor) &&  /Version\/5\./.test(navigator.userAgent) && /Win/.test(navigator.platform);
			if(is_bugged_safari) this.hasAjaxUpload=false;
			//----------------------------------------------------------------------------------

			
			//if does not support html5 upload, test if supports flash upload
			if(!this.hasAjaxUpload)
			{
				try 
				{
					var fo = new ActiveXObject('ShockwaveFlash.ShockwaveFlash');
					if(fo) this.hasFlash = true;
				}catch(e){
					if(navigator.mimeTypes ["application/x-shockwave-flash"] != undefined) this.hasFlash = true;
				}
				
				this.settings.maxConnections = 0; // no parallel uploads on old browsers
			}
			
			//at the end test if supports only html4 standard upload
			this.hasHtml4 = (!this.hasFlash && !this.hasAjaxUpload);
			
			this.useFormData = window.FormData !== undefined;
			
			//if formData is supported we use that, better in general FF4+, Chrome, Safari
			var isfirefox5 =  (navigator.userAgent).match(/Firefox\/(\d+)?/);
			if(isfirefox5!==null)
			{
				var fire_ver = isfirefox5!==null && isfirefox5[1]!==undefined && !isNaN(isfirefox5[1]) ? parseFloat(isfirefox5[1]) : 7;
				if(fire_ver<=6) this.useFormData = false;
			}
			
			//same for some version of opera
			var is_opera =  (navigator.userAgent).match(/Opera\/(\d+)?/);
			if(is_opera!==null)
			{
				var ver = (navigator.userAgent).match(/Version\/(\d+)?/);
				var opera_ver = ver[1]!==undefined && !isNaN(ver[1]) ? parseFloat(ver[1]) : 0;
				if(opera_ver<12.10) this.useFormData = false;
			}
		},
		renderHtml: function()
		{
			var settings 	= this.settings;
			this.mainWrapper  	= $('<div class="ax-main-container" />').append('<h5 class="ax-main-title">' + _('Select Files') + '</h5>').appendTo(this.container);
			
			//get max size bytes in real format
			this.max_size 	= settings.maxFileSize;
			var mult 		= settings.maxFileSize.slice(-1);
			if(isNaN(mult))
			{
				this.max_size = this.max_size.replace(mult, '');//remove the last char
				switch (mult)//1024 or 1000??
				{
					case 'P': this.max_size = this.max_size*1024;
					case 'T': this.max_size = this.max_size*1024;
					case 'G': this.max_size = this.max_size*1024;
					case 'M': this.max_size = this.max_size*1024;
					case 'K': this.max_size = this.max_size*1024;
				}
			}
			
			var bs_browse = 'ax-browse-c ax-button';
			var bs_upload = 'ax-upload-all ax-button';
			var bs_remove = 'ax-clear ax-button';
			
			var bs_b_icon	= 'ax-plus-icon ax-icon';
			var bs_u_icon	= 'ax-upload-icon ax-icon';
			var bs_r_icon	= 'ax-clear-icon ax-icon';
			
			if(settings.bootstrap)
			{
				bs_browse += ' btn btn-primary';
				bs_upload += ' btn btn-success';
				bs_remove += ' btn btn-danger';
				
				bs_b_icon += ' icon-plus-sign';
				bs_u_icon += ' icon-play';
				bs_r_icon += ' icon-remove-sign';
			}
			
			this.browse_c = $('<a class="'+bs_browse+'" title="' + _('Add files') + '" />').append('<span class="'+bs_b_icon+'"></span> <span class="ax-text">' + _('Add files') + '</span>').appendTo(this.mainWrapper);
			
			//Browser control
			this.browseFiles = $('<input type="file" class="ax-browse" name="ax_file_input" />').prop('multiple', (this.hasAjaxUpload && this.settings.maxFiles!=1) ).appendTo(this.browse_c);
			
			//experimental feature, works only on google chrome, has some perfomance issue, upload directory
			if(settings.uploadDir)
			{
				this.browseFiles.prop({'directory':'directory', 'webkitdirectory':'webkitdirectory', 'mozdirectory':'mozdirectory'});
			}
			
			//Browse container for the browse control
			if(this.hasFlash)
			{
				//remove the normal html browse in this case to add the flash one
				this.browse_c.children('.ax-browse').remove();
				
				//give to the flash element an id
				var flash_id = this.container.attr('id')+'_flash';
				
				//standard cross-browser flash html code
				var flash_html = 	'<!--[if !IE]> -->'+
				'<object style="position:absolute;width:150px;height:100px;left:0px;top:0px;z-index:1000;" id="'+flash_id+'" type="application/x-shockwave-flash" data="'+settings.flash+'" width="150" height="100">'+
				'<!-- <![endif]-->'+
				'<!--[if IE]>'+
				'<object style="position:absolute;width:150px;height:100px;left:0px;top:0px;z-index:1000;" id="'+flash_id+'" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"  codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="150" height="100">'+
				'<param name="movie" value="'+settings.flash+'" />'+
				'<!--><!--dgx-->'+
				'<param name="flashvars" value="instance_id='+this.container.attr('id')+'">'+
				'<param name="allowScriptAccess" value="always" />'+
				'<param value="transparent" name="wmode">'+
				'</object>'+
				'<!-- <![endif]-->';
									
				
				this.browse_c.append('<div style="position:absolute;overflow:hidden;width:150px;height:100px;left:0px;top:0px;z-index:0;">'+flash_html+'</div>');

				//keep a reference to the flash object for calling actionscript methods from javascript
				this.flashObj = document.getElementById(flash_id);
			}
					
			//Upload all button
		    this.uploadFiles = $('<a class="'+bs_upload+'" title="' + _('Upload all files') + '" />').append('<span class="'+bs_u_icon+'"></span> <span class="ax-text">' + _('Start upload') + '</span>').appendTo(this.mainWrapper);
		    
		    //remove files button
		    this.removeFiles = $('<a class="'+bs_remove+'" title="' + _('Remove all') + '" />').append('<span class="'+bs_r_icon+'"></span> <span class="ax-text">' + _('Remove all') + '</span>').appendTo(this.mainWrapper);

		    //file list container
		    this.fileList	= $('<ul class="ax-file-list" />').appendTo(this.mainWrapper);
		    if(settings.bootstrap)
		    {
		    	this.fileList.addClass('media-list');
		    }
		},
		//============Bind the events method===============\\
		bindEvents: function()
		{
		    //Add browse event
			var settings = this.settings;
			
		    this.browseFiles.bind('change', this, function(e){
		    	var AU = e.data;
		    	//do not open on flash support, flash has his own browse file
		    	if(AU.settings.enable && !AU.hasFlash)
		    	{
					//if is supported ajaxupload then we have an array of files, if not we have a simple input element with one file
					var files = (AU.hasAjaxUpload) ? this.files : new Array(this);
					AU.addFiles(files);
			    	
			    	if(!AU.hasAjaxUpload)
			    	{
			    		//clone element for next file select
			    		$(this).clone(true).val('').appendTo(AU.browse_c);
			    	}
			    	else
			    	{
			    		//chrome fix change event
			    		this.value = '';
			    	}
		    	}
			});
		    
		    
		    //upload files
		    this.uploadFiles.bind('click', this, function(e){
		    	if(e.data.settings.enable) e.data.uploadAll();
		    	return false;
		    });
		    
		    //remove all files from list
		    this.removeFiles.bind('click', this, function(e){
		    	if(e.data.settings.enable) e.data.clearQueue();
		    	return false;
		    });
		    
		    //external form integration
			//If used with form combination, the bind upload on form submit
			if($(settings.form).length>0)
			{
				this.form = $(settings.form);
			}
			else if(settings.form=='parent')
			{
				this.form = this.container.parents('form:first');
			}   
			
			if(this.form !==null && this.form!==undefined)
			{
				//hide upload buttons on form
				if(settings.hideUploadForm){
					this.uploadFiles.hide();
				}
				
				//if form has any binded any submit event by the user store it and execute at the end
				var events = this.form.data("events");
				if(events!==null && events!==undefined)
				{
					if(events.submit!==null && events.submit!==undefined)
					{
						this.form_submit_event = events.submit;
					}
				}
		        
				//now unbind form events
				this.form.unbind('submit');
				
				//before submit form first upload the files
				this.form.bind('submit.ax', this, function(e){
					if(e.data.files.length>0)
					{
						e.data.uploadAll();
						return false;
					}
				});
			}
			
		    //create drop area if is setted and if is supported
		    if(this.hasAjaxUpload)
		    {
		    	var dropArea = (settings.dropArea=='self')? this.container[0]: $(settings.dropArea)[0];
		    	var me = this;
		    	//change the text to drag&drop
		    	if(settings.dropArea == 'self')
		    	{
		    		this.mainWrapper.find('.ax-main-title').html(_('Select Files or Drag&Drop Files'));
		    	}
		    	
			    //Prevent default and stop propagation on dragenter
		    	dropArea.addEventListener('dragenter',function(e){
		    		e.stopPropagation();  
		    		e.preventDefault(); 
		    	},false);
		    	
		    	//on drag over change background color
		    	dropArea.addEventListener('dragover', function(e){    		
		    		e.stopPropagation();  
		    		e.preventDefault(); 
		    		if(me.settings.enable)
		    		{
		    			if(settings.dropClass) 
		    				$(this).addClass(settings.dropClass);
		    			else
		    				this.style.backgroundColor=settings.dropColor; 
		    			
		    		}
		    	},false);
		    	
		    	//on drag leave reset background color
		    	dropArea.addEventListener('dragleave', function(e){
		    		e.stopPropagation();  
		    		e.preventDefault(); 
		    		if(me.settings.enable)
		    		{
		    			if(settings.dropClass) 
		    				$(this).removeClass(settings.dropClass);
		    			else
		    				this.style.backgroundColor = '';   
		    		}
		    	},false);
		    	
		    	//on drop add files to list
		    	
		    	dropArea.addEventListener('drop', function(e)
			    {
		    		if(me.settings.enable)
		    		{
				    	e.stopPropagation();  
				    	e.preventDefault();
		
				    	//add files
				    	me.addFiles(e.dataTransfer.files);
		
				    	//reset background color
						this.style.backgroundColor = '';
						
						//if autostart is enabled then start upload
				    	if(settings.autoStart)
				    	{
				    		me.uploadAll();
				    	}
		    		}
				},false);	
		    	
		    	//bind the ESC button to close the preview of image, unbind to avoid multiple call when have multiple instance of the uploader
	    		$(document).unbind('.ax').bind('keyup.ax',function(e){
	    			if (e.keyCode == 27) {
	    				$('#ax-box-shadow, #ax-box').fadeOut(500);
	    			}  
	    		});
		    }
		    
		    //load enable option
		    this.enable(this.settings.enable);	
		},
		
		//========================Finish function, runs when all files get uploaded====================\\
		finish: function()
		{
			this.upload_all = false;
			//collect file names in a array
			var fileNames 	= []; //file names
			
			for(var i = 0; i < this.files.length; i++)
			{
				fileNames.push(this.files[i].name);
			}
			
			//to the finish event return the file names and files object
			this.settings.finish.call(this, fileNames, this.files);
	
			//run the before submit event, in case of form integration
			this.settings.beforeSubmit.call(this, fileNames, this.files, function(){
				//if there is a form integrated then submit the form and append files informations
				if(this.form!==null && this.form!==undefined)
				{
					//add to the form the file paths
					var basepath = (typeof(this.settings.remotePath)=='function')?this.settings.remotePath():this.settings.remotePath;
					
					for(var i=0;i<fileNames.length;i++)
					{
						var filepath = basepath+fileNames[i];
						this.form.append('<input name="ax-uploaded-files[]" type="hidden" value="'+filepath+'" />');
					}
					
					this.form.unbind('submit.ax');//remove ajax uploader event
					
					//bind his original submit event
					if(this.form_submit_event!==null && this.form_submit_event!==undefined)
					{
						this.form.bind('submit', this.form_submit_event);
					}
					
					var has_submit_button = this.form.find('[type="submit"]');//strange if form has submit button cannot call .submit()
	
					if(has_submit_button.length>0)
						has_submit_button.trigger('click');//trigger click on the submit button of the form
					else 
						this.form.submit();//submit the form normally now
				}
			});
		},
		
		//======================= Add selected files to list ====================\\
		addFiles: function(files)
		{
			var current_selected_files = [];//store this just for the on select event
			
			//add selected files to the queue
			for (var i = 0; i < files.length; i++) 
			{
				var ext, name, size;
				
				//get file name and file extenstion
				if(this.hasAjaxUpload || this.hasFlash)
				{
					name	= files[i].name;
					size	= files[i].size;
				}
				else
				{
					name	= files[i].value.replace(/^.*\\/, '');
					size	= 0;
				}
	
				//normalizze extension
				ext	= name.split('.').pop().toLowerCase();
	
				//check if extension is allowed to be uploaded 
				//if we have reach the max number of files allowed
				//if file size is allowed
				var err = this.checkFile(name, size);
				
				//if no errors add file to list
				if(err == '')
				{
					//create the file object
					var fileObj = new fileObject(files[i], name, size, ext, this );
					//put in queue
					this.files.push( fileObj );
					current_selected_files.push( fileObj );
				}
				else
				{
					//if there are errors call the error event (if defined from the user)
					this.settings.error.call(this, err, name);
				}
			}
			
			//call the onSelect event, on the selected files
			this.settings.onSelect.call(this, current_selected_files);
	
			//if autostart is enabled then start upload
	    	if(this.settings.autoStart)
	    	{
	    		this.uploadAll();
	    	}
		},
		
		//=================== Check file method on file select action ======================\\
		checkFile: function(name, size)
		{
			var ext	= name.split('.').pop().toLowerCase();
			
			//check max file number
			var max_num_f 	= !!(this.files.length < this.settings.maxFiles);
			
			//check extension
			var allow_ext 	= !!($.inArray(ext, this.settings.allowExt)>=0 || this.settings.allowExt.length==0);
			
			//check file size
			var max_size 	= !!(size<=this.max_size);
			
			//check user validate file function
			var user_error = typeof(this.settings.validateFile)==='function' ? this.settings.validateFile.call(this, name, ext, size):'';
			
			var error = '';
			if(!max_num_f)	error=error+_('Max files number reached')+':' + max_num_f +"\n";
			if(!allow_ext)	error=error+_('Extension not allowed')+':' + ext +"\n";
			if(!max_size)	error=error+_('File size now allowed')+':' + size + "\n";
			if(user_error!='' && user_error!==undefined && user_error!==null) error = error+user_error;
			
			return error;
		},
		getPendingFiles: function()
		{
			var arr = [];
			for(var i = 0;i < this.files.length; i++)
			{
				if(this.files[i].status == 0 && this.slots <= this.settings.maxConnections)
				{
					arr.push(this.files[i]);
					this.slots++;
				}
			}
			return arr;
		},
		//=================== Start the upload of all files======================\\
		uploadAll: function()
		{
			this.upload_all = true;
			//call the beforeUploadAll event, if return false do not upload
			var valid = this.settings.beforeUploadAll.call(this, this.files);
			if(valid!==false)
			{
				var pending = this.getPendingFiles();
				for(var i = 0; i<pending.length;i++)
				{
					pending[i].startUpload();
				}
			}
		},
		
		//=================== Remove files from the list==========================\\
		clearQueue: function()
	    {
	    	while(this.files.length>0){
	    		this.removeFile(0);
	    	}
	    },
	    
	    //=================== This method formats the params to be passed to the server script==========================\\
	    // encode option is for sending data encoded in case data are sended by GET method, (old Ajax Upload without form data, FF3.6)
	    getParams: function(file_name, size, encode)
	    {
	    	//NOTE: all internal params of Real Ajax Uploader starts with ax-
			var settings = this.settings;
			var getpath	= (typeof(settings.remotePath)=='function')?settings.remotePath():settings.remotePath;
			var params	= [];
			
			//file data
			params.push('ax-file-path=' + (encode ? encodeURIComponent(getpath): getpath) );
			params.push('ax-allow-ext=' + (encode ? encodeURIComponent( settings.allowExt.join('|')) : settings.allowExt.join('|')) );
			params.push('ax-file-name=' + (encode ? encodeURIComponent(file_name) : file_name) );
			params.push('ax-max-file-size=' + settings.maxFileSize);
			params.push('ax-file-size=' + size);
			
			//thumb data, for generation of thumbs in the server side
			params.push('ax-thumbPostfix=' + (encode ? encodeURIComponent(settings.thumbPostfix) : settings.thumbPostfix) );
			params.push('ax-thumbPath=' + (encode ? encodeURIComponent(settings.thumbPath) : settings.thumbPath) );
			params.push('ax-thumbFormat=' + (encode ? encodeURIComponent(settings.thumbFormat) : settings.thumbFormat) );
			params.push('ax-thumbHeight=' + settings.thumbHeight);
			params.push('ax-thumbWidth=' + settings.thumbWidth);
			params.push('ax-random='+ (Math.random()*10001) );
			//override or not
			if( this.settings.checkFileExists || this.settings.overrideFile){
				params.push('ax-override=1');
			}
			//user send data, maybe a string like param=value&param2=value2 or json object: {param: value}
			var otherdata	= (typeof(settings.data)=='function')?settings.data():settings.data;
			if(typeof(otherdata)=='object')
			{
				for(var i in otherdata)
				{
					params.push( i + '=' + (encode ? encodeURIComponent(otherdata[i]) : otherdata[i]) );
				}
			}
			else if(typeof(otherdata)=='string' && otherdata!='')
			{
				var pp = otherdata.split('&');
				for(var i = 0; i<pp.length;i++)
				{
					params.push(pp[i]);
				}
			}
			
			return params;
	    },

	    //============================== Remove a file from the list =====================\\
	    //pos the index position of the file
	    removeFile: function(pos)
		{
			var fileobj = this.files[pos];//get the file to remove
			fileobj.stopUpload();//stop upload if the files is being upload
			fileobj.li.remove();//remove the visual LI
			fileobj.file = null;//remove file dom object
			this.files.splice(pos, 1);//remove the file from the array (same is done in flash internal list)
			
			//remove the file from flash list
			if(this.hasFlash)
			{
				this.flashObj.removeFile(pos);
			}
			//re-calculate file positions
			for(var i=0; i<this.files.length; i++)
			{
				this.files[i].pos = i;
			}
		},
		
		//============================= Stop all uploads========================\\
		stopUpload: function()
		{
			for(var i = 0; i<this.files.lenght; i++){
				this.files[i].stopUpload();
			}
		},

		//============================= Format size of files=====================\\
		formatSize: function(size)
		{
			var precision = this.settings.precision;
			
			if (typeof(precision) =='undefined' ) precision = 2;
			
			var suffix = new Array(_('Bytes'), _('KB'), _('MB'), _('GB'));
			var i=0;
			
		    while (size >= 1024 && (i < (suffix.length - 1))) {
		        size /= 1024;
		        i++;
		    }
		    var intVal = Math.round(size);
		    var multp = Math.pow(10, precision);
		    var floor = Math.round((size*multp) % multp);
		    return intVal+'.'+floor+' '+suffix[i];
		},
		//============================== Method for getting/setting options =====================\\
		options: function(opt, val)
		{
			if(val!==undefined && val!==null)//if val is defined then set the option
			{
				this.settings[opt] = val;
				if(opt == 'enable')
				{
					this.enable(val);
				}
			}
			else//if not return the value of that option
			{
				return this.settings[opt];
			}
		},
		
		//============================== Method for enable/disable the uploader =====================\\
		enable: function(bool)
		{
			this.settings.enable = bool;
			if(bool)
			{
				this.container.removeClass('ax-disabled').find('input').attr('disabled',false);
			}
			else
			{
				this.container.addClass('ax-disabled').find('input').attr('disabled',true);
			}
		}
	};
	

	
	//==============================Jquery Bind==============================================\\
	
    var globalSettings = 
    {
    	remotePath : 	'uploads/',					//remote upload path, can be set also in the php upload script
    	url:			'upload.php',				//php/asp/jsp upload script
    	flash:			'uploader.swf',				//flash uploader url for not html5 browsers
    	data:			'',							//other user data to send in GET to the php script
    	async:			true,						//set asyncron upload or not
    	maxFiles:		9999,						//max number of files can be selected
    	allowExt:		[],							//array of allowed upload extesion, can be set also in php script
    	success:		function(fn){ },				//function that triggers every time a file is uploaded
    	finish:			function(file_names, file_obj){ },			//function that triggers when all files are uploaded
    	error:			function(err, fn){ },		//function that triggers if an error occuors during upload,
    	enable:			true,						//start plugin enable or disabled
    	chunkSize:		1048576,					//default 1Mb,	//if supported send file to server by chunks, not at once
    	maxConnections:	3,							//max parallel connection on multiupload recomended 3, firefox support 6, only for browsers that support file api
    	dropColor:		'red',						//back color of drag & drop area, hex or rgb
    	dropClass:		'ax-drop',					//class to add to the drop area when dropping files
    	dropArea:		'self',						//set the id or element of area where to drop files. default self
    	autoStart:		false,						//if true upload will start immediately after drop of files or select of files
    	thumbHeight:	0,							//max thumbnial height if set generate thumbnial of images on server side
    	thumbWidth:		0,							//max thumbnial width if set generate thumbnial of images on server side
    	thumbPostfix:	'_thumb',					//set the post fix of generated thumbs, default filename_thumb.ext,
    	thumbPath:		'',							//set the path where thumbs should be saved, if empty path setted as remotePath
    	thumbFormat:	'',							//default same as image, set thumb output format, jpg, png, gif
    	maxFileSize:	'10M',						//max file size of single file,
    	form:			null,						//integration with some form, set the form selector or object, and upload will start on form submit
    	hideUploadForm:	true,						//hide upload button on form integration, upload starts on form submit
    	beforeSubmit: 	function(file_names, file_obj, formsubmitcall){
    		formsubmitcall.call(this);
    	},				//event that runs before submiting a form
    	editFilename:	false,						//if true allow edit file names before upload, by dblclick
		beforeUpload:	function(filename, file){return true;}, //this function runs before upload start for each file, if return false the upload does not start
    	beforeUploadAll:function(files){return true;}, //this function runs before upload all start, can be good for validation
    	onSelect: 		function(files){},			//function that trigger after a file select has been made, paramter total files in the queue
    	onInit:			function(AU){},				//function that trigger on uploader initialization. Usefull if need to hide any button before uploader set up, without using css
    	language:		'auto',							//set regional language, default is english, avaiables: sq_AL, it_IT
    	uploadDir:		false,						//experimental feature, works on google chrome, for upload an entire folder content
    	removeOnSuccess:false,						//if true remove the file from the list after has been uploaded successfully
    	removeOnError:	false,						//if true remove the file from the list if it has errors during upload
    	bootstrap:		false,						//tell if to use bootstrap for theming buttons
    	previews:		true,						//disable previews of images . to avoid memory problem with browsers
    	
    	validateFile: 	function(name, extension, size){ //user define function to validate a file, must return a string with error
    		
    	},
    	
    	//3.0 added
    	overrideFile: 	false,						//false=> do not ovveride on server side, true==> over, function let user decide
    	checkFileExists: false,						//false=> do not ask user for file exits, true=> ask user to override or not the file
    	fileInfo: function(oData)
		{
		    var strPretty = "";
		    for (var a in oData) {
		        if (oData.hasOwnProperty(a)) {
		            if (typeof oData[a] == "object") {
		                strPretty += a + " : [" + oData[a].length + " values]\r\n";
		            } else {
		                strPretty += a + " : " + oData[a] + "\r\n";
		            }
		        }
		    }
		    alert(strPretty);
		},											//function that get bind on info button of file, return file exif data (for images)
    	allowDelete:	false						//if enabled allow user to delete file after upload NOTE: should also be enabled from server side for security reason
    };
    
	var methods =
	{
		init : function(options)
		{
    	    return this.each(function() 
    	    {
				var settings = $.extend({}, globalSettings, options);
				//for avoiding two times call errors
				var $this 	= $(this).html('');
				var AU		= $this.data('AU');
				if( AU!==undefined && AU!==null)
				{
					return;
				}
				//create the uploader object
				$this.data('AU', new AjaxUploader($this, settings));
    	    });
		},
		clear:function()
		{
			return this.each(function()
			{
				var $this = $(this);
				var AU = $this.data('AU');
				AU.clearQueue();
			});
		},
		start:function()
		{
			return this.each(function()
			{
				var $this = $(this);
				var AU = $this.data('AU');
				AU.uploadAll();
			});
		},
		addFlash:function(files)
		{
			var $this = $(this);
			var AU = $this.data('AU');
			AU.addFiles(files);
		},
		progressFlash: function(p, filepos)
		{
			var $this = $(this);
			var AU = $this.data('AU');
			AU.files[filepos].onProgress(p);
		},
		onFinishFlash: function(json, pos)
		{
			var $this = $(this);
			var AU = $this.data('AU');
			AU.uploading = false;
			try
			{
				var json_ret = jQuery.parseJSON(json);
				if(parseInt(json_ret.status) == -1)
				{
					throw json_ret.info;
				}
				else
				{
					AU.files[pos].onFinishUpload(json_ret.name, json_ret.size, json_ret.status, json_ret.info);
				}
			}
			catch(err)
			{
				AU.files[pos].onError(err);
			}
		},
		getUrl: function(name, size)
		{
			var $this 	= $(this);
			var AU 		= $this.data('AU');
			return AU.settings.url;
		},
		getParams: function(name, size)
		{
			var $this 	= $(this);
			var AU 		= $this.data('AU');
			var params	= AU.getParams(name, size, true);
			return params.join('&');
		},
		getAllowedExt: function(asArray)
		{
			var $this = $(this);
			var AU = $this.data('AU');
			var allowedExt = AU.settings.allowExt;
			
			return (asArray===true)?allowedExt:allowedExt.join('|');
		},
		getMaxFileNum: function(asArray)
		{
			var AU = $(this).data('AU');
			return AU.settings.maxFiles;
		},
		checkFile: function(name, size)
		{
			var AU = $(this).data('AU');
			return AU.checkFile(name, size) == '';
		},
		checkEnable: function(){
			return $(this).data('AU').settings.enable;
		},
		getFiles: function(){
			var AU = $(this).data('AU');
			return AU.files;
		},
		enable:function()
		{
			return this.each(function()
			{
				var AU = $(this).data('AU');
				AU.enable(true);
			});
		},
		disable:function()
		{
			return this.each(function()
			{
				var AU = $(this).data('AU');
				AU.enable(false);
			});
		},
		destroy : function()
		{
			return this.each(function()
			{
				var $this = $(this);
				var AU = $this.data('AU');//get ajax uploader object
				AU.clearQueue();//remove files in queue
				$this.removeData('AU').html('');//remove object and empty element
			});
		},
		option : function(option, value)
		{
			return this.each(function(){
				var AU = $(this).data('AU');
				return AU.options(option, value);
			});
		},
		debug: function(msg){
			//console.log(msg);
		}
	};


	//jquery standard recomendation write of plugins
	$.fn.ajaxupload = function(method, options)
	{
		if(methods[method])
		{
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		}
		else if(typeof method === 'object' || !method)
		{
			return methods.init.apply(this, arguments);
		}
		else
		{
			$.error('Method ' + method + ' does not exist on jQuery.AjaxUploader');
		}
	};

})(jQuery);



///////EXTERNAL PLUGINSSS

/*
 * Javascript EXIF Reader - jQuery plugin 0.1.4
 * Copyright (c) 2008 Jacob Seidelin, cupboy@gmail.com, http://blog.nihilogic.dk/
 * Licensed under the MPL License [http://www.nihilogic.dk/licenses/mpl-license.txt]
 * Edit from Alban Xhaferllari file reader http://www.albanx.com
 */

(function($) {
	
	function _arrayBufferToBase64( buffer ) {
	    var binary = '';
	    var bytes = new Uint8Array( buffer );
	    var len = bytes.byteLength;
	    for (var i = 0; i < len; i++) {
	        binary += String.fromCharCode( bytes[ i ] );
	    }
	    return binary;
	}
	
	
	var BinaryFile = function(strData, iDataOffset, iDataLength) {
		if (typeof strData == "object") {
	    	strData = _arrayBufferToBase64(strData); 
		}
	    var data = strData;
	    var dataOffset = iDataOffset || 0;
	    var dataLength = 0;
	
	    this.getRawData = function() {
	        return data;
	    };
	
	    if (typeof strData == "string") {
	        dataLength = iDataLength || data.length;
	
	        this.getByteAt = function(iOffset) {
	            return data.charCodeAt(iOffset + dataOffset) & 0xFF;
	        };
	    }else if (typeof strData == "unknown") {
	        dataLength = iDataLength || IEBinary_getLength(data);
	
	        this.getByteAt = function(iOffset) {
	            return IEBinary_getByteAt(data, iOffset + dataOffset);
	        };
	    }
	
	    this.getLength = function() {
	        return dataLength;
	    };
	
	    this.getSByteAt = function(iOffset) {
	        var iByte = this.getByteAt(iOffset);
	        if (iByte > 127)
	            return iByte - 256;
	        else
	            return iByte;
	    };
	
	    this.getShortAt = function(iOffset, bBigEndian) {
	        var iShort = bBigEndian ?
	            (this.getByteAt(iOffset) << 8) + this.getByteAt(iOffset + 1)
	            : (this.getByteAt(iOffset + 1) << 8) + this.getByteAt(iOffset);
	        if (iShort < 0) iShort += 65536;
	        return iShort;
	    };
	    this.getSShortAt = function(iOffset, bBigEndian) {
	        var iUShort = this.getShortAt(iOffset, bBigEndian);
	        if (iUShort > 32767)
	            return iUShort - 65536;
	        else
	            return iUShort;
	    };
	    this.getLongAt = function(iOffset, bBigEndian) {
	        var iByte1 = this.getByteAt(iOffset),
	            iByte2 = this.getByteAt(iOffset + 1),
	            iByte3 = this.getByteAt(iOffset + 2),
	            iByte4 = this.getByteAt(iOffset + 3);
	
	        var iLong = bBigEndian ?
	            (((((iByte1 << 8) + iByte2) << 8) + iByte3) << 8) + iByte4
	            : (((((iByte4 << 8) + iByte3) << 8) + iByte2) << 8) + iByte1;
	        if (iLong < 0) iLong += 4294967296;
	        return iLong;
	    };
	    this.getSLongAt = function(iOffset, bBigEndian) {
	        var iULong = this.getLongAt(iOffset, bBigEndian);
	        if (iULong > 2147483647)
	            return iULong - 4294967296;
	        else
	            return iULong;
	    };
	    this.getStringAt = function(iOffset, iLength) {
	        var aStr = [];
	        for (var i=iOffset,j=0;i<iOffset+iLength;i++,j++) {
	            aStr[j] = String.fromCharCode(this.getByteAt(i));
	        }
	        return aStr.join("");
	    };
	
	    this.getCharAt = function(iOffset) {
	        return String.fromCharCode(this.getByteAt(iOffset));
	    };
	    this.toBase64 = function() {
	        return window.btoa(data);
	    };
	    this.fromBase64 = function(strBase64) {
	        data = window.atob(strBase64);
	    };
	};
	
	var EXIF = {};
	
	(function() {
	var bDebug = false;
	
	EXIF.Tags = {
	
	    // version tags
	    0x9000 : "ExifVersion",         // EXIF version
	    0xA000 : "FlashpixVersion",     // Flashpix format version
	
	    // colorspace tags
	    0xA001 : "ColorSpace",          // Color space information tag
	
	    // image configuration
	    0xA002 : "PixelXDimension",     // Valid width of meaningful image
	    0xA003 : "PixelYDimension",     // Valid height of meaningful image
	    0x9101 : "ComponentsConfiguration", // Information about channels
	    0x9102 : "CompressedBitsPerPixel",  // Compressed bits per pixel
	
	    // user information
	    0x927C : "MakerNote",           // Any desired information written by the manufacturer
	    0x9286 : "UserComment",         // Comments by user
	
	    // related file
	    0xA004 : "RelatedSoundFile",        // Name of related sound file
	
	    // date and time
	    0x9003 : "DateTimeOriginal",        // Date and time when the original image was generated
	    0x9004 : "DateTimeDigitized",       // Date and time when the image was stored digitally
	    0x9290 : "SubsecTime",          // Fractions of seconds for DateTime
	    0x9291 : "SubsecTimeOriginal",      // Fractions of seconds for DateTimeOriginal
	    0x9292 : "SubsecTimeDigitized",     // Fractions of seconds for DateTimeDigitized
	
	    // picture-taking conditions
	    0x829A : "ExposureTime",        // Exposure time (in seconds)
	    0x829D : "FNumber",         // F number
	    0x8822 : "ExposureProgram",     // Exposure program
	    0x8824 : "SpectralSensitivity",     // Spectral sensitivity
	    0x8827 : "ISOSpeedRatings",     // ISO speed rating
	    0x8828 : "OECF",            // Optoelectric conversion factor
	    0x9201 : "ShutterSpeedValue",       // Shutter speed
	    0x9202 : "ApertureValue",       // Lens aperture
	    0x9203 : "BrightnessValue",     // Value of brightness
	    0x9204 : "ExposureBias",        // Exposure bias
	    0x9205 : "MaxApertureValue",        // Smallest F number of lens
	    0x9206 : "SubjectDistance",     // Distance to subject in meters
	    0x9207 : "MeteringMode",        // Metering mode
	    0x9208 : "LightSource",         // Kind of light source
	    0x9209 : "Flash",           // Flash status
	    0x9214 : "SubjectArea",         // Location and area of main subject
	    0x920A : "FocalLength",         // Focal length of the lens in mm
	    0xA20B : "FlashEnergy",         // Strobe energy in BCPS
	    0xA20C : "SpatialFrequencyResponse",    //
	    0xA20E : "FocalPlaneXResolution",   // Number of pixels in width direction per FocalPlaneResolutionUnit
	    0xA20F : "FocalPlaneYResolution",   // Number of pixels in height direction per FocalPlaneResolutionUnit
	    0xA210 : "FocalPlaneResolutionUnit",    // Unit for measuring FocalPlaneXResolution and FocalPlaneYResolution
	    0xA214 : "SubjectLocation",     // Location of subject in image
	    0xA215 : "ExposureIndex",       // Exposure index selected on camera
	    0xA217 : "SensingMethod",       // Image sensor type
	    0xA300 : "FileSource",          // Image source (3 == DSC)
	    0xA301 : "SceneType",           // Scene type (1 == directly photographed)
	    0xA302 : "CFAPattern",          // Color filter array geometric pattern
	    0xA401 : "CustomRendered",      // Special processing
	    0xA402 : "ExposureMode",        // Exposure mode
	    0xA403 : "WhiteBalance",        // 1 = auto white balance, 2 = manual
	    0xA404 : "DigitalZoomRation",       // Digital zoom ratio
	    0xA405 : "FocalLengthIn35mmFilm",   // Equivalent foacl length assuming 35mm film camera (in mm)
	    0xA406 : "SceneCaptureType",        // Type of scene
	    0xA407 : "GainControl",         // Degree of overall image gain adjustment
	    0xA408 : "Contrast",            // Direction of contrast processing applied by camera
	    0xA409 : "Saturation",          // Direction of saturation processing applied by camera
	    0xA40A : "Sharpness",           // Direction of sharpness processing applied by camera
	    0xA40B : "DeviceSettingDescription",    //
	    0xA40C : "SubjectDistanceRange",    // Distance to subject
	
	    // other tags
	    0xA005 : "InteroperabilityIFDPointer",
	    0xA420 : "ImageUniqueID"        // Identifier assigned uniquely to each image
	};
	
	EXIF.TiffTags = {
	    0x0100 : "ImageWidth",
	    0x0101 : "ImageHeight",
	    0x8769 : "ExifIFDPointer",
	    0x8825 : "GPSInfoIFDPointer",
	    0xA005 : "InteroperabilityIFDPointer",
	    0x0102 : "BitsPerSample",
	    0x0103 : "Compression",
	    0x0106 : "PhotometricInterpretation",
	    0x0112 : "Orientation",
	    0x0115 : "SamplesPerPixel",
	    0x011C : "PlanarConfiguration",
	    0x0212 : "YCbCrSubSampling",
	    0x0213 : "YCbCrPositioning",
	    0x011A : "XResolution",
	    0x011B : "YResolution",
	    0x0128 : "ResolutionUnit",
	    0x0111 : "StripOffsets",
	    0x0116 : "RowsPerStrip",
	    0x0117 : "StripByteCounts",
	    0x0201 : "JPEGInterchangeFormat",
	    0x0202 : "JPEGInterchangeFormatLength",
	    0x012D : "TransferFunction",
	    0x013E : "WhitePoint",
	    0x013F : "PrimaryChromaticities",
	    0x0211 : "YCbCrCoefficients",
	    0x0214 : "ReferenceBlackWhite",
	    0x0132 : "DateTime",
	    0x010E : "ImageDescription",
	    0x010F : "Make",
	    0x0110 : "Model",
	    0x0131 : "Software",
	    0x013B : "Artist",
	    0x8298 : "Copyright"
	};
	
	EXIF.GPSTags = {
	    0x0000 : "GPSVersionID",
	    0x0001 : "GPSLatitudeRef",
	    0x0002 : "GPSLatitude",
	    0x0003 : "GPSLongitudeRef",
	    0x0004 : "GPSLongitude",
	    0x0005 : "GPSAltitudeRef",
	    0x0006 : "GPSAltitude",
	    0x0007 : "GPSTimeStamp",
	    0x0008 : "GPSSatellites",
	    0x0009 : "GPSStatus",
	    0x000A : "GPSMeasureMode",
	    0x000B : "GPSDOP",
	    0x000C : "GPSSpeedRef",
	    0x000D : "GPSSpeed",
	    0x000E : "GPSTrackRef",
	    0x000F : "GPSTrack",
	    0x0010 : "GPSImgDirectionRef",
	    0x0011 : "GPSImgDirection",
	    0x0012 : "GPSMapDatum",
	    0x0013 : "GPSDestLatitudeRef",
	    0x0014 : "GPSDestLatitude",
	    0x0015 : "GPSDestLongitudeRef",
	    0x0016 : "GPSDestLongitude",
	    0x0017 : "GPSDestBearingRef",
	    0x0018 : "GPSDestBearing",
	    0x0019 : "GPSDestDistanceRef",
	    0x001A : "GPSDestDistance",
	    0x001B : "GPSProcessingMethod",
	    0x001C : "GPSAreaInformation",
	    0x001D : "GPSDateStamp",
	    0x001E : "GPSDifferential"
	};
	
	EXIF.StringValues = {
	    ExposureProgram : {
	        0 : "Not defined",
	        1 : "Manual",
	        2 : "Normal program",
	        3 : "Aperture priority",
	        4 : "Shutter priority",
	        5 : "Creative program",
	        6 : "Action program",
	        7 : "Portrait mode",
	        8 : "Landscape mode"
	    },
	    MeteringMode : {
	        0 : "Unknown",
	        1 : "Average",
	        2 : "CenterWeightedAverage",
	        3 : "Spot",
	        4 : "MultiSpot",
	        5 : "Pattern",
	        6 : "Partial",
	        255 : "Other"
	    },
	    LightSource : {
	        0 : "Unknown",
	        1 : "Daylight",
	        2 : "Fluorescent",
	        3 : "Tungsten (incandescent light)",
	        4 : "Flash",
	        9 : "Fine weather",
	        10 : "Cloudy weather",
	        11 : "Shade",
	        12 : "Daylight fluorescent (D 5700 - 7100K)",
	        13 : "Day white fluorescent (N 4600 - 5400K)",
	        14 : "Cool white fluorescent (W 3900 - 4500K)",
	        15 : "White fluorescent (WW 3200 - 3700K)",
	        17 : "Standard light A",
	        18 : "Standard light B",
	        19 : "Standard light C",
	        20 : "D55",
	        21 : "D65",
	        22 : "D75",
	        23 : "D50",
	        24 : "ISO studio tungsten",
	        255 : "Other"
	    },
	    Flash : {
	        0x0000 : "Flash did not fire",
	        0x0001 : "Flash fired",
	        0x0005 : "Strobe return light not detected",
	        0x0007 : "Strobe return light detected",
	        0x0009 : "Flash fired, compulsory flash mode",
	        0x000D : "Flash fired, compulsory flash mode, return light not detected",
	        0x000F : "Flash fired, compulsory flash mode, return light detected",
	        0x0010 : "Flash did not fire, compulsory flash mode",
	        0x0018 : "Flash did not fire, auto mode",
	        0x0019 : "Flash fired, auto mode",
	        0x001D : "Flash fired, auto mode, return light not detected",
	        0x001F : "Flash fired, auto mode, return light detected",
	        0x0020 : "No flash function",
	        0x0041 : "Flash fired, red-eye reduction mode",
	        0x0045 : "Flash fired, red-eye reduction mode, return light not detected",
	        0x0047 : "Flash fired, red-eye reduction mode, return light detected",
	        0x0049 : "Flash fired, compulsory flash mode, red-eye reduction mode",
	        0x004D : "Flash fired, compulsory flash mode, red-eye reduction mode, return light not detected",
	        0x004F : "Flash fired, compulsory flash mode, red-eye reduction mode, return light detected",
	        0x0059 : "Flash fired, auto mode, red-eye reduction mode",
	        0x005D : "Flash fired, auto mode, return light not detected, red-eye reduction mode",
	        0x005F : "Flash fired, auto mode, return light detected, red-eye reduction mode"
	    },
	    SensingMethod : {
	        1 : "Not defined",
	        2 : "One-chip color area sensor",
	        3 : "Two-chip color area sensor",
	        4 : "Three-chip color area sensor",
	        5 : "Color sequential area sensor",
	        7 : "Trilinear sensor",
	        8 : "Color sequential linear sensor"
	    },
	    SceneCaptureType : {
	        0 : "Standard",
	        1 : "Landscape",
	        2 : "Portrait",
	        3 : "Night scene"
	    },
	    SceneType : {
	        1 : "Directly photographed"
	    },
	    CustomRendered : {
	        0 : "Normal process",
	        1 : "Custom process"
	    },
	    WhiteBalance : {
	        0 : "Auto white balance",
	        1 : "Manual white balance"
	    },
	    GainControl : {
	        0 : "None",
	        1 : "Low gain up",
	        2 : "High gain up",
	        3 : "Low gain down",
	        4 : "High gain down"
	    },
	    Contrast : {
	        0 : "Normal",
	        1 : "Soft",
	        2 : "Hard"
	    },
	    Saturation : {
	        0 : "Normal",
	        1 : "Low saturation",
	        2 : "High saturation"
	    },
	    Sharpness : {
	        0 : "Normal",
	        1 : "Soft",
	        2 : "Hard"
	    },
	    SubjectDistanceRange : {
	        0 : "Unknown",
	        1 : "Macro",
	        2 : "Close view",
	        3 : "Distant view"
	    },
	    FileSource : {
	        3 : "DSC"
	    },
	
	    Components : {
	        0 : "",
	        1 : "Y",
	        2 : "Cb",
	        3 : "Cr",
	        4 : "R",
	        5 : "G",
	        6 : "B"
	    }
	};
	
	function addEvent(oElement, strEvent, fncHandler)
	{
	    if (oElement.addEventListener) {
	        oElement.addEventListener(strEvent, fncHandler, false);
	    } else if (oElement.attachEvent) {
	        oElement.attachEvent("on" + strEvent, fncHandler);
	    }
	}
	
	
	function imageHasData(oImg)
	{
	    return !!(oImg.exifdata);
	}

	
	function findEXIFinJPEG(oFile) {
	    if (oFile.getByteAt(0) != 0xFF || oFile.getByteAt(1) != 0xD8) {
	        return false; // not a valid jpeg
	    }
	
	    var iOffset = 2;
	    var iLength = oFile.getLength();
	    while (iOffset < iLength) {
	        if (oFile.getByteAt(iOffset) != 0xFF) {
	            if (bDebug) console.log("Not a valid marker at offset " + iOffset + ", found: " + oFile.getByteAt(iOffset));
	            return false; // not a valid marker, something is wrong
	        }
	
	        var iMarker = oFile.getByteAt(iOffset+1);
	
	        // we could implement handling for other markers here,
	        // but we're only looking for 0xFFE1 for EXIF data
	
	        if (iMarker == 22400) {
	            if (bDebug) console.log("Found 0xFFE1 marker");
	            return readEXIFData(oFile, iOffset + 4, oFile.getShortAt(iOffset+2, true)-2);
	            // iOffset += 2 + oFile.getShortAt(iOffset+2, true);
	            // WTF?
	
	        } else if (iMarker == 225) {
	            // 0xE1 = Application-specific 1 (for EXIF)
	            if (bDebug) console.log("Found 0xFFE1 marker");
	            return readEXIFData(oFile, iOffset + 4, oFile.getShortAt(iOffset+2, true)-2);
	
	        } else {
	            iOffset += 2 + oFile.getShortAt(iOffset+2, true);
	        }
	
	    }
	
	}
	
	
	function readTags(oFile, iTIFFStart, iDirStart, oStrings, bBigEnd)
	{
	    var iEntries = oFile.getShortAt(iDirStart, bBigEnd);
	    var oTags = {};
	    for (var i=0;i<iEntries;i++) {
	        var iEntryOffset = iDirStart + i*12 + 2;
	        var strTag = oStrings[oFile.getShortAt(iEntryOffset, bBigEnd)];
	        if (!strTag && bDebug) console.log("Unknown tag: " + oFile.getShortAt(iEntryOffset, bBigEnd));
	        oTags[strTag] = readTagValue(oFile, iEntryOffset, iTIFFStart, iDirStart, bBigEnd);
	    }
	    return oTags;
	}
	
	
	function readTagValue(oFile, iEntryOffset, iTIFFStart, iDirStart, bBigEnd)
	{
	    var iType = oFile.getShortAt(iEntryOffset+2, bBigEnd);
	    var iNumValues = oFile.getLongAt(iEntryOffset+4, bBigEnd);
	    var iValueOffset = oFile.getLongAt(iEntryOffset+8, bBigEnd) + iTIFFStart;
	
	    switch (iType) {
	        case 1: // byte, 8-bit unsigned int
	        case 7: // undefined, 8-bit byte, value depending on field
	            if (iNumValues == 1) {
	                return oFile.getByteAt(iEntryOffset + 8, bBigEnd);
	            } else {
	                var iValOffset = iNumValues > 4 ? iValueOffset : (iEntryOffset + 8);
	                var aVals = [];
	                for (var n=0;n<iNumValues;n++) {
	                    aVals[n] = oFile.getByteAt(iValOffset + n);
	                }
	                return aVals;
	            }
	            break;
	
	        case 2: // ascii, 8-bit byte
	            var iStringOffset = iNumValues > 4 ? iValueOffset : (iEntryOffset + 8);
	            return oFile.getStringAt(iStringOffset, iNumValues-1);
	            // break;
	
	        case 3: // short, 16 bit int
	            if (iNumValues == 1) {
	                return oFile.getShortAt(iEntryOffset + 8, bBigEnd);
	            } else {
	                var iValOffset = iNumValues > 2 ? iValueOffset : (iEntryOffset + 8);
	                var aVals = [];
	                for (var n=0;n<iNumValues;n++) {
	                    aVals[n] = oFile.getShortAt(iValOffset + 2*n, bBigEnd);
	                }
	                return aVals;
	            }
	            // break;
	
	        case 4: // long, 32 bit int
	            if (iNumValues == 1) {
	                return oFile.getLongAt(iEntryOffset + 8, bBigEnd);
	            } else {
	                var aVals = [];
	                for (var n=0;n<iNumValues;n++) {
	                    aVals[n] = oFile.getLongAt(iValueOffset + 4*n, bBigEnd);
	                }
	                return aVals;
	            }
	            break;
	        case 5: // rational = two long values, first is numerator, second is denominator
	            if (iNumValues == 1) {
	                return oFile.getLongAt(iValueOffset, bBigEnd) / oFile.getLongAt(iValueOffset+4, bBigEnd);
	            } else {
	                var aVals = [];
	                for (var n=0;n<iNumValues;n++) {
	                    aVals[n] = oFile.getLongAt(iValueOffset + 8*n, bBigEnd) / oFile.getLongAt(iValueOffset+4 + 8*n, bBigEnd);
	                }
	                return aVals;
	            }
	            break;
	        case 9: // slong, 32 bit signed int
	            if (iNumValues == 1) {
	                return oFile.getSLongAt(iEntryOffset + 8, bBigEnd);
	            } else {
	                var aVals = [];
	                for (var n=0;n<iNumValues;n++) {
	                    aVals[n] = oFile.getSLongAt(iValueOffset + 4*n, bBigEnd);
	                }
	                return aVals;
	            }
	            break;
	        case 10: // signed rational, two slongs, first is numerator, second is denominator
	            if (iNumValues == 1) {
	                return oFile.getSLongAt(iValueOffset, bBigEnd) / oFile.getSLongAt(iValueOffset+4, bBigEnd);
	            } else {
	                var aVals = [];
	                for (var n=0;n<iNumValues;n++) {
	                    aVals[n] = oFile.getSLongAt(iValueOffset + 8*n, bBigEnd) / oFile.getSLongAt(iValueOffset+4 + 8*n, bBigEnd);
	                }
	                return aVals;
	            }
	            break;
	    }
	}
	
	
	function readEXIFData(oFile, iStart, iLength)
	{
	    if (oFile.getStringAt(iStart, 4) != "Exif") {
	        if (bDebug) console.log("Not valid EXIF data! " + oFile.getStringAt(iStart, 4));
	        return false;
	    }
	
	    var bBigEnd;
	
	    var iTIFFOffset = iStart + 6;
	
	    // test for TIFF validity and endianness
	    if (oFile.getShortAt(iTIFFOffset) == 0x4949) {
	        bBigEnd = false;
	    } else if (oFile.getShortAt(iTIFFOffset) == 0x4D4D) {
	        bBigEnd = true;
	    } else {
	        if (bDebug) console.log("Not valid TIFF data! (no 0x4949 or 0x4D4D)");
	        return false;
	    }
	
	    if (oFile.getShortAt(iTIFFOffset+2, bBigEnd) != 0x002A) {
	        if (bDebug) console.log("Not valid TIFF data! (no 0x002A)");
	        return false;
	    }
	
	    if (oFile.getLongAt(iTIFFOffset+4, bBigEnd) != 0x00000008) {
	        if (bDebug) console.log("Not valid TIFF data! (First offset not 8)", oFile.getShortAt(iTIFFOffset+4, bBigEnd));
	        return false;
	    }
	
	    var oTags = readTags(oFile, iTIFFOffset, iTIFFOffset+8, EXIF.TiffTags, bBigEnd);
	
	    if (oTags.ExifIFDPointer) {
	        var oEXIFTags = readTags(oFile, iTIFFOffset, iTIFFOffset + oTags.ExifIFDPointer, EXIF.Tags, bBigEnd);
	        for (var strTag in oEXIFTags) {
	            switch (strTag) {
	                case "LightSource" :
	                case "Flash" :
	                case "MeteringMode" :
	                case "ExposureProgram" :
	                case "SensingMethod" :
	                case "SceneCaptureType" :
	                case "SceneType" :
	                case "CustomRendered" :
	                case "WhiteBalance" :
	                case "GainControl" :
	                case "Contrast" :
	                case "Saturation" :
	                case "Sharpness" :
	                case "SubjectDistanceRange" :
	                case "FileSource" :
	                    oEXIFTags[strTag] = EXIF.StringValues[strTag][oEXIFTags[strTag]];
	                    break;
	
	                case "ExifVersion" :
	                case "FlashpixVersion" :
	                    oEXIFTags[strTag] = String.fromCharCode(oEXIFTags[strTag][0], oEXIFTags[strTag][1], oEXIFTags[strTag][2], oEXIFTags[strTag][3]);
	                    break;
	
	                case "ComponentsConfiguration" :
	                    oEXIFTags[strTag] =
	                        EXIF.StringValues.Components[oEXIFTags[strTag][0]]
	                        + EXIF.StringValues.Components[oEXIFTags[strTag][1]]
	                        + EXIF.StringValues.Components[oEXIFTags[strTag][2]]
	                        + EXIF.StringValues.Components[oEXIFTags[strTag][3]];
	                    break;
	            }
	            oTags[strTag] = oEXIFTags[strTag];
	        }
	    }
	
	    if (oTags.GPSInfoIFDPointer) {
	        var oGPSTags = readTags(oFile, iTIFFOffset, iTIFFOffset + oTags.GPSInfoIFDPointer, EXIF.GPSTags, bBigEnd);
	        for (var strTag in oGPSTags) {
	            switch (strTag) {
	                case "GPSVersionID" :
	                    oGPSTags[strTag] = oGPSTags[strTag][0]
	                        + "." + oGPSTags[strTag][1]
	                        + "." + oGPSTags[strTag][2]
	                        + "." + oGPSTags[strTag][3];
	                    break;
	            }
	            oTags[strTag] = oGPSTags[strTag];
	        }
	    }
	
	    return oTags;
	}
	
	EXIF.getTag = function(oImg, strTag)
	{
	    if (!imageHasData(oImg)) return;
	    return oImg.exifdata[strTag];
	};
	
	EXIF.getAllTags = function(oImg)
	{
	    if (!imageHasData(oImg)) return {};
	    var oData = oImg.exifdata;
	    var oAllTags = {};
	    for (var a in oData) {
	        if (oData.hasOwnProperty(a)) {
	            oAllTags[a] = oData[a];
	        }
	    }
	    return oAllTags;
	};
	
	EXIF.pretty = function(oImg)
	{
	    if (!imageHasData(oImg)) return "";
	    var oData = oImg.exifdata;
	    var strPretty = "";
	    for (var a in oData) {
	        if (oData.hasOwnProperty(a)) {
	            if (typeof oData[a] == "object") {
	                strPretty += a + " : [" + oData[a].length + " values]\r\n";
	            } else {
	                strPretty += a + " : " + oData[a] + "\r\n";
	            }
	        }
	    }
	    return strPretty;
	};
	
	EXIF.readFromBinaryFile = function(oFile) {
	    return findEXIFinJPEG(oFile);
	};
	
	EXIF.getFilePart = function(file) {
	    if (file.slice) {
	        filePart = file.slice(0, 131072);
	    } else if (file.webkitSlice) {
	        filePart = file.webkitSlice(0, 131072);
	    } else if (file.mozSlice) {
	        filePart = file.mozSlice(0, 131072);
	    } else {
	        filePart = file;
	    }
	
	    return filePart;
	};
	
	// load data for images manually
	$.fn.exif = function(strTag) {
	    var aStrings = [];
	    this.each(function() {
	        aStrings.push(EXIF.getTag(this, strTag));
	    });
	    return aStrings;
	};
	
	$.fn.exifAll = function() {
	    var aStrings = [];
	    this.each(function() {
	        aStrings.push(EXIF.getAllTags(this));
	    });
	    return aStrings;
	};
	
	$.fn.exifPretty = function() {
	    var aStrings = [];
	    this.each(function() {
	        aStrings.push(EXIF.pretty(this));
	    });
	    return aStrings;
	};
	
	$.fn.fileExif = function(callback) 
	{
	    var reader = new FileReader();
	    reader.onload = function(event) {
	        var binaryResponse = new BinaryFile(event.target.result);
	        
	        callback(EXIF.readFromBinaryFile(binaryResponse));
	    };
	    
	    reader.readAsArrayBuffer(EXIF.getFilePart(this[0].files[0]));
	};

	$.fileExif = function(file, callback)
	{       
		var reader = new FileReader();
	    reader.onload = function(event){
	    	var binaryResponse = new BinaryFile(event.target.result); 
	    	callback(EXIF.readFromBinaryFile(binaryResponse));
	    };
	    reader.readAsArrayBuffer(EXIF.getFilePart(file));	
	};

})();

})(jQuery);