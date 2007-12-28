         function dofrm(form,msg,noreset,callback) {
            msg = msg ? msg : 'Sending...';
            $('ajax-response'+form.id).innerHTML = msg;
            var url = form.action ? form.action : window.location.href; 
            var data = form.serialize();
				if(!callback) callback = function(){};
            new Ajax.Updater('ajax-response'+form.id, url, {
               parameters: data + '&ajax=1',
					onComplete: callback
            });
            if(!noreset) form.reset();
         }//end function dofrm
