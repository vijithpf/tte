package {
	
	import flash.display.*;
	import flash.events.*;
	import flash.text.*;
	import flash.text.TextField; 
	
	import flash.net.FileReference;
	import flash.net.FileReferenceList;
	import flash.net.FileFilter;
	import flash.net.URLRequest;
	import flash.net.URLVariables;
	import flash.net.URLRequestMethod;
	import flash.utils.Timer;
	import flash.events.TimerEvent;
	import flash.external.*; 
	
	public class Uploader extends MovieClip {
		
		var fileSelect:FileReferenceList;
		var fileList:Array;
		var instance_id:String;
		var currUpload:int;
		
		//for debug
		private var output:TextField;
		
		public function Uploader()
		{
			//ExternalInterface.call("jQuery('#"+instance_id+"').ajaxupload", "debug", "Start");
			instance_id = stage.loaderInfo.parameters["instance_id"];
			fileSelect 	= new FileReferenceList();
			fileList 	= new Array();
			
			fileSelect.addEventListener( Event.SELECT, onSelectFiles );
			select_btn.addEventListener( MouseEvent.CLICK, browseFiles );

			ExternalInterface.addCallback("uploadFile", uploadFile);
			ExternalInterface.addCallback("removeFile", removeFile);
			ExternalInterface.addCallback("stopUpload", stopUpload);
					
			output = new TextField();
            output.y = 0;
			output.x = 5;
            output.width = 450;
            output.height = 325;
            output.multiline = true;
            output.wordWrap = true;
            output.border = true;
            //addChild(output);
		}
		
		private function onSelectFiles( e:Event )
		{
			try
			{
				var item:FileReference;
							
				var to_add:Array = new Array();
				for(var i=0; i<fileSelect.fileList.length; i++)
				{
					item = fileSelect.fileList[i];
					var valid:Boolean = ExternalInterface.call("jQuery('#"+instance_id+"').ajaxupload", "checkFile", item.name, item.size);
					if(valid)
					{
						setup(item);
						fileList.push(item);
						to_add.push({'name':item.name, 'size':item.size});
					}
					else
					{
						ExternalInterface.call("jQuery('#"+instance_id+"').ajaxupload", "debug", "Check file return false");
					}
				}
				
				ExternalInterface.call("jQuery('#"+instance_id+"').ajaxupload", "addFlash", to_add);
			}
			catch (ex:Error) 
			{
				ExternalInterface.call("jQuery('#"+instance_id+"').ajaxupload", "debug", ex.toString());
            }
		}
		
		private function browseFiles(e:Event)
		{
			try 
			{
				var is_enable:Boolean = ExternalInterface.call("jQuery('#"+instance_id+"').ajaxupload", "checkEnable");
				if(is_enable)
				{
					var allowedExt:String = ExternalInterface.call("jQuery('#"+instance_id+"').ajaxupload", "getAllowedExt").toString();
	
					var extArr = allowedExt.split('|');
					var filter:Array = new Array();
					for(var i=0; i<extArr.length; i++)
					{
						var ext:String = extArr[i];
						if(ext!='')
							filter.push('*.'+ext);
					}
					
					if(filter.length>0)
						fileSelect.browse( [new FileFilter( filter.join(', '), filter.join(';') )] );
					else
						fileSelect.browse();
				}
			}
			catch (errObject:Error) {
				fileSelect.browse();
			}
		}
		
		private function uploadEnd(event:DataEvent):void 
		{
			try
			{
				var item:FileReference = fileList[currUpload];
				ExternalInterface.call("jQuery('#"+instance_id+"').ajaxupload", "onFinishFlash", event.data, currUpload);
				item.removeEventListener(DataEvent.UPLOAD_COMPLETE_DATA, this.uploadEnd);
			}
			catch (ex:Error) 
			{
				ExternalInterface.call("jQuery('#"+instance_id+"').ajaxupload", "debug", ex.toString());
            }
		}

		
		/**
		* Single file upload action
		*/
		private function uploadFile(pos:int)
		{
			var item:FileReference = fileList[pos];
			try 
			{
				if(item)
				{
					currUpload = pos;
					var req:URLRequest 	= new URLRequest();
					req.method 			= URLRequestMethod.POST;
					req.url 			= ExternalInterface.call("jQuery('#"+instance_id+"').ajaxupload", "getUrl", item.name, item.size).toString();

					var jsparams:String	= ExternalInterface.call("jQuery('#"+instance_id+"').ajaxupload", "getParams", item.name, item.size).toString();
					var reqVars:URLVariables = new URLVariables(jsparams);

					req.data = reqVars;
					item.addEventListener(DataEvent.UPLOAD_COMPLETE_DATA, this.uploadEnd);
					item.addEventListener(IOErrorEvent.IO_ERROR, this.io_error);
					item.addEventListener(SecurityErrorEvent.SECURITY_ERROR, this.security_error);

					item.upload(req, 'ax_file_input', false);
				}
			}
			catch (ex:Error) 
			{
			 	//output.text=errObject.getStackTrace();
				ExternalInterface.call("jQuery('#"+instance_id+"').ajaxupload", "debug", ex.toString());
			}
		}
		
		//remove file from list
		private function removeFile(pos:int)
		{
			this.stopUpload(pos);
			fileList.splice(pos,1);
		}
		
		
		private function stopUpload(pos:int)
		{
			//may fail so use try catch
			try {
				var file:FileReference = fileList[pos];
				file.cancel();
				file.removeEventListener(DataEvent.UPLOAD_COMPLETE_DATA, this.uploadEnd);
			}
			catch (errObject:Error) {
			}
		}
		
		private function setup( file:FileReference )
		{
			//file.addEventListener( Event.CANCEL, cancel_func );
			file.addEventListener( Event.COMPLETE, this.complete_func );
			file.addEventListener( IOErrorEvent.IO_ERROR, this.io_error );
			file.addEventListener( Event.OPEN, this.open_func );
			file.addEventListener( ProgressEvent.PROGRESS, this.progress_func );
		}
		
		private function cancel_func( e:Event )
		{
			//something to do on cancel
		}
		
		private function complete_func( e:Event )
		{
			//trace( 'File Uploaded' );
		}
		
		private function io_error( e:IOErrorEvent )
		{
			ExternalInterface.call("jQuery('#"+instance_id+"').ajaxupload", "debug", "IO error");
		}
		
		private function security_error( )
		{
			ExternalInterface.call("jQuery('#"+instance_id+"').ajaxupload", "debug", "Security error");
		}
		
		private function open_func( e:Event )
		{
			
		}
		
		private function progress_func( e:ProgressEvent )
		{
			var pr = Math.round( (e.bytesLoaded/e.bytesTotal)*100);
			ExternalInterface.call("jQuery('#"+instance_id+"').ajaxupload('progressFlash', "+pr+","+currUpload+")");
		}
	}	
}