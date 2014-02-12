(function($){
    
    var RawEditor = function(element)
    {
       var elem = $(element);
       var obj = this;
       var pid = 0;
       var zone = 0;
       var open = false;
       var state = 'closed';
       var closeonly = false;

        this.update = function()
        {
            $.jGrowl("updating content, please wait", {
                life: 3000, 
                speed:  'slow'
            });
            var post_data = {
                zone_data: $("#editor-"+this.zone).val()
            };
            var post_url = "http://"+$(location).attr('hostname')+"/update_zone/"+this.zone+"/"+this.pid;
            jQuery.ajax({
                type: "POST",
                url: post_url,
                dataType: "json",
                data: post_data,
                cache: false
            }).done(function( responce ) {
                if(responce.status == 'ok'){
                    $.jGrowl("Zone update sucess", {
                        life: 3000, 
                        speed:  'slow'
                    });
                }
            });
        };
        
        this.history = function()
        {
            
            var post_data = {
                zone: this.zone,
                pid: this.pid
            };
            var post_url = "http://"+$(location).attr('hostname')+"/zone_history/"
            jQuery.ajax({
                type: "POST",
                url: post_url,
                dataType: "json",
                data: post_data,
                cache: false
            }).done(function( responce ) {
                for (x in responce){
                    var d = new Date(0);
                    d.setUTCSeconds(responce[x].z_date);
                    $('#editor-raw-history_'+responce[x].z_parent).append($("<option/>", { 
                        value: responce[x].z_id,
                        text : d.getMonth()+"/"+d.getDate()+"/"+d.getFullYear()+" "+d.getHours()+":"+d.getMinutes()+" "+responce[x].username
                    }));
                }
            });
        };
        
        
        this.fetch_history = function(zid)
        {
            var post_data = {
                z_id: zid
            };
            var post_url = "http://"+$(location).attr('hostname')+"/zone_history_data/"
            jQuery.ajax({
                type: "POST",
                url: post_url,
                dataType: "json",
                data: post_data,
                cache: false
            }).done(function( responce ) {
                var editor = new RawEditor();
                $("#editor-"+responce[0].z_parent).val(editor.decode_data(responce[0].z_data));
            });
        };
        
        this.decode_data = function(str){
            return decodeURIComponent((str+'').replace(/\+/g, '%20'));
        }
       
        this.toggle = function(){
            if(this.state == 'justclosed'){
                this.open = false;
                this.close();
            }else{
                if(!this.open){
                    this.open = true;
                    this.attach();
                }else{
                    this.open = false;
                    this.close();
                }
            }
        }
        
        this.close = function(){
            switch(this.state){
                case 'justclosed':
                    this.state = 'closed';
                    break;
                case 'open':
                    this.state = 'justclosed';
                    break;
                default: 
                    this.state = 'closed';
            }
            
            if(this.open){
                var content = $("#editor-"+this.zone).val();
                $("#"+this.zone).html( content );
            }
            this.open = false;
            $('#raw-edit-button').html("Enable Raw Editor");
        }
        
        this.attach = function(){
            this.state = 'open';
            var content = $("#"+this.zone).html();
            var data = {
                Zone: this.zone,
                ZoneData: content
            };
            this.history();
            $( "#"+this.zone ).html($.tmpl( raweditorTemplate, data ));
            //$("#editor-"+this.zone).width( $("#"+this.zone).parent().width() );
            //$("#editor-"+this.zone).height( 400 );

            $("#zoneupdate_"+this.zone).click({zone: this.zone},function(e) {
                $('#'+e.data.zone).data('raweditor').update();
            });
            
            $('#editor-raw-history_'+this.zone).change({target: this},function(e) {
                e.data.target.fetch_history($('#editor-raw-history_'+e.data.target.zone).val());
            });
            $('#raw-edit-button').html("Disable Raw Editor");
        }
    };
    $.fn.raweditor = function(action, pid)
   {
       return this.each(function()
       {
           var element = $(this);
           if (!element.data('raweditor')){
                var raweditor = new RawEditor(this);
                Temporal.reg_editor(raweditor);
                element.data('raweditor', raweditor);
                raweditor.pid = pid;
                raweditor.zone = $(this).attr('id');
           }
           
           switch(action){
               case 'update':
                   element.data('raweditor').update();
                   break;
               case 'close':
                   element.data('raweditor').close();
                   break;
               case 'toggle':
                   element.data('raweditor').toggle();
                   break;
               default:
                   element.data('raweditor').toggle();
                   break;
           }
       });
   };
})(jQuery);
