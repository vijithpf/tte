!function(t){t.fn.imlazy=function(a){function n(){var a=f.filter(function(){var a=t(this),n=g.scrollTop(),e=n+g.height(),i=a.offset().top,r=i+a.height();return r>=n&&e>=i});l=a.trigger("reveal"),f=f.not(l),f.length?"":g.off("scroll resize lookup",n)}function e(){t(c).each(function(n,e){function r(t){return t.indexOf("%")?parseFloat(t.replace("%","")/100)<.3?-.5:t.replace("%","")/100:"top"==t?-.5:"bottom"==t?1:t?void 0:.5}var s=t(e).find("canvas.bg-blur")[0],l=t(e),g=l.outerWidth(!0),c=l.outerHeight(!0),f=l.find(".bg-small").is("img")?.5:-.5,u=.5,d=t("<img></img>",{src:l.find(".bg-small").is("img")?l.find(".bg-small").attr("src"):l.find(".bg-small").css("background-image").slice(4,-1).replace(/"/g,"")});if(l.find(".bg-small").not("img")){var m=l.find(".bg-small").css("background-position").split(" ");u=m[0]?r(m[0]):.5,f=m[1]?r(m[1]):.5}s.height=c,s.width=g;var h=s.getContext("2d");d.on("load",function(){o(h,this,0,0,g,c,u,f),i(h,0,0,g,c,a)})})}function i(t,a,n,e,i,o){if(!(isNaN(o)||1>o)){o|=0;var s,l=(document.getElementById(t),t);try{try{s=l.getImageData(a,n,e,i)}catch(g){try{netscape.security.PrivilegeManager.enablePrivilege("UniversalBrowserRead"),s=l.getImageData(a,n,e,i)}catch(g){throw alert("Cannot access local image"),new Error("unable to access local image data: "+g)}}}catch(g){throw alert("Cannot access image"),new Error("unable to access image data: "+g)}var c,f,m,h,b,v,w,p,x,y,C,I,T,k,z,D,E,B,M,N,P=s.data,j=o+o+1,F=e-1,H=i-1,O=o+1,Q=O*(O+1)/2,R=new r,U=R;for(m=1;j>m;m++)if(U=U.next=new r,m==O)var W=U;U.next=R;var q=null,A=null;w=v=0;var G=u[o],J=d[o];for(f=0;i>f;f++){for(k=z=D=p=x=y=0,C=O*(E=P[v]),I=O*(B=P[v+1]),T=O*(M=P[v+2]),p+=Q*E,x+=Q*B,y+=Q*M,U=R,m=0;O>m;m++)U.r=E,U.g=B,U.b=M,U=U.next;for(m=1;O>m;m++)h=v+((m>F?F:m)<<2),p+=(U.r=E=P[h])*(N=O-m),x+=(U.g=B=P[h+1])*N,y+=(U.b=M=P[h+2])*N,k+=E,z+=B,D+=M,U=U.next;for(q=R,A=W,c=0;e>c;c++)P[v]=p*G>>J,P[v+1]=x*G>>J,P[v+2]=y*G>>J,p-=C,x-=I,y-=T,C-=q.r,I-=q.g,T-=q.b,h=w+((h=c+o+1)<F?h:F)<<2,k+=q.r=P[h],z+=q.g=P[h+1],D+=q.b=P[h+2],p+=k,x+=z,y+=D,q=q.next,C+=E=A.r,I+=B=A.g,T+=M=A.b,k-=E,z-=B,D-=M,A=A.next,v+=4;w+=e}for(c=0;e>c;c++){for(z=D=k=x=y=p=0,v=c<<2,C=O*(E=P[v]),I=O*(B=P[v+1]),T=O*(M=P[v+2]),p+=Q*E,x+=Q*B,y+=Q*M,U=R,m=0;O>m;m++)U.r=E,U.g=B,U.b=M,U=U.next;for(b=e,m=1;o>=m;m++)v=b+c<<2,p+=(U.r=E=P[v])*(N=O-m),x+=(U.g=B=P[v+1])*N,y+=(U.b=M=P[v+2])*N,k+=E,z+=B,D+=M,U=U.next,H>m&&(b+=e);for(v=c,q=R,A=W,f=0;i>f;f++)h=v<<2,P[h]=p*G>>J,P[h+1]=x*G>>J,P[h+2]=y*G>>J,p-=C,x-=I,y-=T,C-=q.r,I-=q.g,T-=q.b,h=c+((h=f+O)<H?h:H)*e<<2,p+=k+=q.r=P[h],x+=z+=q.g=P[h+1],y+=D+=q.b=P[h+2],q=q.next,C+=E=A.r,I+=B=A.g,T+=M=A.b,k-=E,z-=B,D-=M,A=A.next,v+=e}l.putImageData(s,a,n)}}function r(){this.r=0,this.g=0,this.b=0,this.a=0,this.next=null}function o(t,a,n,e,i,r,o,s){2===arguments.length&&(n=e=0,i=t.canvas.width,r=t.canvas.height),o=o?o:.5,s=s?s:.5,0>o&&(o=0),0>s&&(s=0),o>1&&(o=1),s>1&&(s=1);var l,g,c,f,u=a.width,d=a.height,m=Math.min(i/u,r/d),h=u*m,b=d*m,v=1;i>h&&(v=i/h),r>b&&(v=r/b),h*=v,b*=v,c=u/(h/i),f=d/(b/r),l=(u-c)*o,g=(d-f)*s,0>l&&(l=0),0>g&&(g=0),c>u&&(c=u),f>d&&(f=d),t.drawImage(a,l,g,c,f,n,e,i,r)}function s(t,a){var n=null;return function(){var e=this,i=arguments;clearTimeout(n),n=setTimeout(function(){t.apply(e,i)},a)}}var l,g=t(window),c=this,f=this,a=a||100;this.one("reveal",function(){var a=t(this);if(a.find(".bg-small").is("img")){var n=t("<img></img>",{src:a.find(".bg-small").data("url"),"class":"big-image"});n.on("load",function(){a.find(".bg-small").after(this),setTimeout(function(){a.addClass("loaded")},300)})}else{var n=t("<img></img>",{src:a.find(".bg-small").data("url")});n.on("load",function(){a.find(".bg-lg").css("background-image","url( "+t(this).attr("src")+" )"),setTimeout(function(){a.addClass("loaded")},300)})}});var u=[512,512,456,512,328,456,335,512,405,328,271,456,388,335,292,512,454,405,364,328,298,271,496,456,420,388,360,335,312,292,273,512,482,454,428,405,383,364,345,328,312,298,284,271,259,496,475,456,437,420,404,388,374,360,347,335,323,312,302,292,282,273,265,512,497,482,468,454,441,428,417,405,394,383,373,364,354,345,337,328,320,312,305,298,291,284,278,271,265,259,507,496,485,475,465,456,446,437,428,420,412,404,396,388,381,374,367,360,354,347,341,335,329,323,318,312,307,302,297,292,287,282,278,273,269,265,261,512,505,497,489,482,475,468,461,454,447,441,435,428,422,417,411,405,399,394,389,383,378,373,368,364,359,354,350,345,341,337,332,328,324,320,316,312,309,305,301,298,294,291,287,284,281,278,274,271,268,265,262,259,257,507,501,496,491,485,480,475,470,465,460,456,451,446,442,437,433,428,424,420,416,412,408,404,400,396,392,388,385,381,377,374,370,367,363,360,357,354,350,347,344,341,338,335,332,329,326,323,320,318,315,312,310,307,304,302,299,297,294,292,289,287,285,282,280,278,275,273,271,269,267,265,263,261,259],d=[9,11,12,13,13,14,14,15,15,15,15,16,16,16,16,17,17,17,17,17,17,17,18,18,18,18,18,18,18,18,18,19,19,19,19,19,19,19,19,19,19,19,19,19,19,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24];return g.on("resize",s(e,300)),g.on("scroll resize",n),e(),n(),this}}(window.jQuery);

// call in js
// blur: can pass blur strength, default value is 100
$('.element').imlazy(blur);

//HTML structure
//
//if want to call on image tag
//
//
// <div class="element">
//     <img class="bg-small" data-url="<?php echo $bigImage->src; ?>" src="<?php echo $thumb->src; ?>">
//     <canvas class="bg-blur"></canvas>
// </div>








// if want to call on backgroud image
// 
// 
// <div class="element">
//     <span class="bg-small" data-url="<?php echo $bigImage->src; ?>" style="background-image: url('<?php echo $thumb->src ?>')"></span>
//     <div class="bg-lg"></div>
//     <canvas class="bg-blur"></canvas>
// </div>








// After loading image, "loaded" class will be applied to the element. Later you cnan controll everything using css to show loaded image.