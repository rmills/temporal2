
Temporal = new Object()
Temporal.editors = new Array();
Temporal.reg_editor = function(obj)
{
     this.editors.push(obj);
}
Temporal.close_editors = function()
{
    for (x in this.editors){
        try{
            this.editors[x].close();
        }catch(e){ }
    }
}

