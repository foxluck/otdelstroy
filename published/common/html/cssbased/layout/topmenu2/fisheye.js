k=document;	v=Date;	x=false;	z=Array;	af=Math.floor;	ag=RegExp;	b=new z(14);	
saa=appsArray;
aa=new z(11);ab=10;t=0;u=0;n=0;o=new v();h=5;m=385;c=0;w=x;var title;var firstHoverOccurred=x;m=385;p=0;
var titleTable;

function d(ac){
	c=ac;
	o=new v();
	setTimeout("gidle()",20);
}
function e(ac){
	c=0;
	w=x;
	o=new v();
	setTimeout("gidle()",20);
}

function ae(){
	for(var j=1;j<b.length;j++){
		b[j]=35
	}
	title=k.getElementById('imageTitle');
	titleTable=k.getElementById('titleTable');
	for(i=0;i<b.length;i++){
		aa[i]=new Image();
		aid=saa[i+1];
		if (aa[i].loading != null)
			aa[i].src=img_loader35.src;
		else
			aa[i].src="../../../"+ aid + "/html/img/" + saa[i+1]+".gif"
	}
	setTimeout("gidle()",20);
}

function gidle(){
	var l=0;
	for(var i=1;i<b.length;i++){
		var imagename="image"+i;
		var imageElem=k.getElementById(imagename);
		if(c!=i){if(b[i]>35){b[i]-=h;if(b[i]<=35){
			b[i]=35;aid=saa[i];
			if (imageElem.loading != null)
				imageElem.src=img_loader35.src;
			else
				imageElem.src="../../../"+ aid + "/html/img/" + saa[i]+"35.gif";
		}
		imageElem.width=b[i];
		imageElem.height=b[i];
		if(c==0){
			var g=af(255-255*(b[i]-35)/35);
			title.style.visibility="hidden";
			titleTable.style.display = "none";
			title.style.color="rgb("+g+","+g+","+g+")"}p=1}l+=b[i]
		}
	}
	if(c!=0&&b[c]<100){
		imagename="image"+c;
		imageElem=k.getElementById(imagename);
		if(w==x){
			w=true;
			var y=(c-1)*45;
			title.innerHTML='<img src="../../../common/html/res/images/cleardot.gif" width=' + y + ' height=1><span>' + k.getElementById(imagename).alt+'</span>'
		}
		b[c]+=h;
		p=1;
		if(b[c]>100){
			b[c]=100}l+=b[c];
			if(l<m){b[c]+=m-l;if(b[c]>100){
				b[c]=100
			}
			l=m
		}
		var g=af(255-255*(b[c]-35)/35);
		title.style.visibility="visible";
		titleTable.style.display = "block";
		title.style.color="rgb("+g+","+g+","+g+")";
		imageElem.width=b[c];
		imageElem.height=b[c];
		aid=saa[c];
		if (k.getElementById(imagename).loading != null)
			k.getElementById(imagename).src=img_loader.src;
		else
			k.getElementById(imagename).src="../../../"+ aid + "/html/img/" + saa[c]+".gif"
	}
	m=l;
	var ad=new v();
	ab=ad.getTime()-o.getTime();
	o=ad;
	t+=ab;
	u++;
	n=t/u;h=5;
	if(u>4){
		if(n>30){h=10}
		if(n>60){h=15}
		if(n>90){h=20}
	}
	if(p){setTimeout("gidle()",20);p=0}
}

function changeImage(id) {
	var image = document.getElementById("image" + id);
	image.src = img_loader.src;
	image.loading = true;
	image.style.border = "1px solid #999";
	e(id);
}
var img_loader = new Image();
img_loader.src = "../../../common/html/res/images/ajax-loader100.gif";
var img_loader35 = new Image();
img_loader35.src = "../../../common/html/res/images/ajax-loader35.gif";