<style><!--
.h{font-size:20px} .q{color:#00c} 
#imageTitle span {background: white}
 .Separator {width: 1px; height: 35px; border-right: 1px solid #D0D0D0; margin-left: 8px}
--></style><script type="text/javascript">k=document;v=Date;x=false;z=Array;af=Math.floor;ag=RegExp;b=new z(12);saa=new z("null",<?=$appIdsStr;?>);aa=new z(11);ab=10;t=0;u=0;n=0;o=new v();h=5;m=385;c=0;w=x;var title;var firstHoverOccurred=x;m=385;p=0;function d(ac){c=ac;o=new v();setTimeout("gidle()",20);}function e(ac){c=0;w=x;o=new v();setTimeout("gidle()",20);}function ae(){for(var j=1;j<b.length;j++){b[j]=35}title=k.getElementById('imageTitle');for(i=0;i<b.length;i++){aa[i]=new Image();aid=saa[i+1];aa[i].src="../../../"+ aid + "/html/img/" + saa[i+1]+".gif"}setTimeout("gidle()",20);}function gidle(){var l=0;for(var i=1;i<b.length;i++){var imagename="image"+i;var imageElem=k.getElementById(imagename);if(c!=i){if(b[i]>35){b[i]-=h;if(b[i]<=35){b[i]=35;aid=saa[i];imageElem.src="../../../"+ aid + "/html/img/" + saa[i]+"35.gif"}imageElem.width=b[i];imageElem.height=b[i];if(c==0){var g=af(255-255*(b[i]-35)/35);title.style.visibility="hidden";title.style.color="rgb("+g+","+g+","+g+")"}p=1}l+=b[i]}}if(c!=0&&b[c]<100){imagename="image"+c;imageElem=k.getElementById(imagename);if(w==x){w=true;var y=(c-1)*45;title.innerHTML='<img src="../../../common/html/res/images/cleardot.gif" width=' + y + ' height=1><span>' + k.getElementById(imagename).alt+'</span>'}b[c]+=h;p=1;if(b[c]>100){b[c]=100}l+=b[c];if(l<m){b[c]+=m-l;if(b[c]>100){b[c]=100}l=m}var g=af(255-255*(b[c]-35)/35);title.style.visibility="visible";title.style.color="rgb("+g+","+g+","+g+")";imageElem.width=b[c];imageElem.height=b[c];aid=saa[c];k.getElementById(imagename).src="../../../"+ aid + "/html/img/" + saa[c]+".gif"}m=l;var ad=new v();ab=ad.getTime()-o.getTime();o=ad;t+=ab;u++;n=t/u;h=5;if(u>4){if(n>30){h=10}if(n>60){h=15}if(n>90){h=20}}if(p){setTimeout("gidle()",20);p=0}}</script>
</script>

	
<div style='position: absolute; bottom: 5px; padding-left: 2px; '>
<table><tr><td id="imageTitle" style="color: rgb(255, 255, 255); font-weight: bold; white-space: nowrap" >&nbsp;</td></tr></table>
<table style='float:left'><tr height="35" valign='bottom'><td>
	<?=$footerContent;?>
</td>
<? if ($needAddServiceLink) { ?><td valign='bottom' nowrap style='padding-bottom: 10px'><a href='<?=$addServiceLink?>'><?=$kernelStrings["app_add_remove_services"];?></a></td><? } ?>
</tr></table>
</div>
<script>
	ae ();
</script>