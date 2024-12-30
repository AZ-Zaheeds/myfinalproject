
const text = "hello!.. hello!.. welcome ausubite!!!......";
let i=0;
const speed = 100; 
const delayBetweenLoops = 1000;

function typewriter(){
    if (i < text. length){
        document.getElementById("typewriter").textContent += text.charAt(i);
        i++;
        setTimeout(typewriter,speed);
    }else{
        setTimeout(()=>{
            document.getElementById("typewriter").textContent = '';
            i = 0;
            typewriter();
        }, delayBetweenLoops);
    }
} 
typewriter();
