function getCookie(Name){ 
var re=new RegExp(Name+"=[^;]+", "i"); //construct RE to search for target name/value pair
if (document.cookie.match(re)) //if cookie found
return document.cookie.match(re)[0].split("=")[1] //return its value
return ""
}

function setCookie(name, value, days){
var expireDate = new Date()
//set "expstring" to either future or past date, to set or delete cookie, respectively
var expstring=expireDate.setDate(expireDate.getDate()+parseInt(days))
document.cookie = name+"="+value+"; expires="+expireDate.toGMTString()+"; path=/";
}

function rememberForm(formid){ //Main remember form values object
this.formref=document.getElementById(formid)? document.getElementById(formid) : document.forms[formid]
this.cookiename=formid
this.persistdays=180 //days to persist form values
this.fields=new Array()
this.cookiestr=""
var forminstance=this
rememberForm.dotask(this.formref, function(){forminstance.savevalues()}, "submit") //save form values onsubmit
rememberForm.dotask(window, function(){forminstance.recallvalues()}, "load") //populate form with saved values onload (body)
}

rememberForm.prototype.getfield=function(attr){ //get form field based on its ID or name attribute
var fieldref=document.getElementById(attr)? document.getElementById(attr) : this.formref[attr]
return fieldref
}

rememberForm.prototype.persistfields=function(){ //get form fields to persist values for
for (var i=0; i<arguments.length; i++){
this.fields[i]=this.getfield(arguments[i])
this.fields[i].fname=arguments[i] //store name or id of field in custom property
}
}

rememberForm.prototype.savevalues=function(){ //get form values and store in cookie
for (var i=0; i<this.fields.length; i++){
if (this.fields[i].type=="text")
this.cookiestr+=this.fields[i].fname+":"+escape(this.fields[i].value)+"#"
}
if (typeof this.togglebox!="undefined"){ //if "remember values checkbox" is defined
this.persistdays=(this.togglebox.checked)? this.persistdays : -1 //decide whether to save form values
this.cookiestr=(this.togglebox.checked)? this.cookiestr+"toggleboxid:on;" : this.cookiestr
}
else //if checkbox isn't defined, just remove final "#" from cookie string
this.cookiestr=this.cookiestr.substr(0, this.cookiestr.length-1)+";"
setCookie(this.cookiename, this.cookiestr, this.persistdays)
}

rememberForm.prototype.recallvalues=function(){ //populate form with saved values
var cookievalue=getCookie(this.cookiename)
if (cookievalue!=""){ //parse cookie, where cookie looks like: field1:value1#field2:value2...
var cookievaluepair=cookievalue.split("#")
for (var i=0; i<cookievaluepair.length; i++){
if (cookievaluepair[i].split(":")[0]!="toggleboxid" && this.getfield(cookievaluepair[i].split(":")[0]).type=="text")
this.getfield(cookievaluepair[i].split(":")[0]).value=unescape(cookievaluepair[i].split(":")[1])
else //else if name in name/value pair is "toggleboxid"
this.togglebox.checked=true
}
}
}

rememberForm.prototype.addtoggle=function(attr){
this.togglebox=this.getfield(attr)
}

//Call this function if you wish to clear the user's cookie of any saved values for this form instantly
rememberForm.prototype.clearcookie=function(){
setCookie(this.cookiename, "", -1)
}

rememberForm.dotask=function(target, functionref, tasktype){
var tasktype=(window.addEventListener)? tasktype : "on"+tasktype
if (target.addEventListener)
target.addEventListener(tasktype, functionref, false)
else if (target.attachEvent)
target.attachEvent(tasktype, functionref)
}
