function uaredirect(murl){
try {
if(document.getElementById("bdmark") != null){
return;
}
var urlhash = window.location.hash;
if (!urlhash.match("fromapp")){
if ((navigator.userAgent.match(/(iPhone|iPod|Android|ios|iPad)/i))) {
location.replace(murl);
}
}
} catch(err){}
}