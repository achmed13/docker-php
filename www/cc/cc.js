var n = document.createElement('div');
n.innerHTML = '<a id="ac1" style="display:none;" href="javascript:acOff();">AC is On |</a> <a id="ac0" href="javascript:acOn(0);">AC is Off |</a> <a id="gcc1"  style="display:none;" href="javascript:gccOff();">GCC is On |</a> <a id="gcc0" href="javascript:gccOn();">GCC is Off |</a> <a id="ab1" style="display:none;" href="javascript:abOff();">AB is On |</a> <a id="ab0" href="javascript:abOn();">AB is Off |</a>';
n.style.cssFloat = 'left';
document.getElementById('links').parentNode.appendChild(n);

var ac; 
var gcc; 
var gcc2; 
var abI = false;
var abInterval = 5000;
var acPs=0;
var interval = 20;
var autoBuy = false;
var autoBuyTxt = autoBuy ? "on" : "off";

if(autoBuy){
	setTimeout(OptimalItem,5000);
	gccOn();
	abOn();
	acOn(100000);
}

var ab = setInterval(function(){
	if(autoBuy){
		autoBuy = false;
		setTimeout(function(){
			autoBuy = true;
			OptimalItem();
		},3000);
	}
},60000);

function acOn(cl){ 
	var ms = 500;
	if(cl==0){
		cl = window.prompt('How Many CPS?', 100000);
	}
	cl = cl  / (1000/ms);
	acPs = cl*(1000/ms);
	ac = autoClicker(cl, ms);
	document.getElementById('ac1').style.display='inline'; 
	document.getElementById('ac0').style.display='none'; 
} 
function acOff(){ 
	clearInterval(ac); 
	acPs = 0;
	document.getElementById('ac1').style.display='none'; 
	document.getElementById('ac0').style.display='inline'; 
} 
function gccOn(){ 
	gcc = setInterval(function() { if (Game.shimmers[0]) { Game.shimmers[0].wrath=0; Game.shimmers[0].l.click(); } }, 1500); 
	document.getElementById('gcc1').style.display='inline'; 
	document.getElementById('gcc0').style.display='none'; 
}
function gccOff(){ 
	clearInterval(gcc); 
	document.getElementById('gcc1').style.display='none'; 
	document.getElementById('gcc0').style.display='inline'; 
} 
function abOn(){ 
	abI = true;
	selected=0;
	document.getElementById('ab1').style.display='inline'; 
	document.getElementById('ab0').style.display='none'; 
}
function abOff(){ 
	abI = false; 
	document.getElementById('ab1').style.display='none'; 
	document.getElementById('ab0').style.display='inline'; 
} 

function autoClicker(clicksAtOnce, repeatInterval) { 
	var cheated = false; 
	var intoTheAbyss = function() { 
		if(!cheated) { 
			cheated = true; 
			for(var i = 0; i < clicksAtOnce; i++) { 
				Game.ClickCookie(); 
				Game.lastClick = 0;
			} 
			cheated = false; 
		} 
	} 
	return setInterval(intoTheAbyss, repeatInterval); 
};


var name;
var price;
var cpsItem;
var selected=0;
var currentCps=Game.cookiesPs;
var selectedItem;
var cnt = 0;
 
document.addEventListener('keydown', function(event) {
    if(event.keyCode == 65) {
        autoBuy = !autoBuy;
        autoBuyTxt = autoBuy ? "on" : "off";
		if(autoBuy){OptimalItem();}
    }
});

function OptimalItem()
{
	var cpc = Number.MAX_VALUE;
	
	var sel;
	var st = Game.UpgradesInStore.length-1;
	st = st > 10 ? 10 : st;
	//if(abI && (selected==0 || cnt % 150 == 0)){
	if(abI){
		cnt=0;
		for(i = st; i >= 0; i--) {
			var cps1 = 0;
			var me = Game.UpgradesInStore[i];
			var x = me.id;
			if (x != 11 && x != 64 && x != 69 && x != 73 && x != 74 && x != 84 && x != 85 && x != 181 && x != 182 && x != 183 && x != 184 && x != 185 && x != 209 && x != 333 && x != 331 && x != 361)
			{
					sel = me;
					selectedItem=sel;
					upgradeName = me.name;
					price = Math.round(me.basePrice);
					selected=1;
				try{
					if(autoBuy && Game.cookies >= price && selected==1){selectedItem.buy();selected=0;}
				}catch(e){
					console.log(e);
				}
					sel = null;
			}
		}
	}
 
	for(i = Game.ObjectsById.length-1; i >= 0; i--){
		var cps1=0;
		var me = Game.ObjectsById[i];
		me.amount++;
		Game.CalculateGains();
		for(j = Game.ObjectsById.length-1; j >= 0; j--){ cps1 += Game.ObjectsById[j].cps(Game.ObjectsById[j])*Game.ObjectsById[j].amount;}
		var cps2 = cps1 * Game.globalCpsMult;
		me.amount--;
		Game.CalculateGains();
		var myCps = cps2 - currentCps;
		var cpsBuilding = me.price *(Game.cookiesPs + myCps) / myCps;
		if (cpsBuilding < cpc && myCps >= 0.1)
		{	
			cpc = cpsBuilding;
			sel = me;
			cpsItem = myCps;
			upgradeName = me.name;
			price = Math.round(me.price);
		}
	}
	currentCps = Game.cookiesPs;
	selected=1;
		selectedItem=sel;
	try{
		if(autoBuy && Game.cookies >= price && selected==1){selectedItem.buy();selected=0;}
	} catch(e) {
		console.log(e);
	}
	Display(upgradeName); 
	cnt++;
	if(autoBuy){setTimeout(OptimalItem,interval);}
}

function Display(upgradeName)
{
	var mCPS = Game.computedMouseCps*acPs;
	var tCPS = Game.cookiesPs + mCPS;
	var time = (price - Game.cookies) / tCPS;
	var txt = "" + upgradeName + "<br>" + Beautify(price) + "<br>" + getHHMMSS(time) + "<br>" + Beautify(acPs) + " : " + Beautify(mCPS) + " /s";
	if(Game.version >= 1.05){
		Game.tickerL.innerHTML = txt;
	} else {
		Game.Ticker = txt;
 	}
	Game.TickerAge = interval;
}
 
function getHHMMSS(seconds){
	seconds = parseInt(seconds,10);
	seconds = seconds > 0 ? seconds : 0;
	var hours=0;
	var minutes=0;
	if(seconds>60){
		hours   = Math.floor(seconds / 3600);
		minutes = Math.floor((seconds - (hours * 3600)) / 60);
		var seconds = seconds - (hours * 3600) - (minutes * 60);
		if (seconds < 10 && seconds >= 0) {seconds = "0"+seconds;}
	}
	var time = seconds;
	if(minutes > 0){
		time = minutes+':'+seconds;
	}
	if(hours > 0){
		if (minutes < 10) {minutes = "0"+minutes;}
		time = hours+':'+minutes+':'+seconds;
	}
    return time;
}
