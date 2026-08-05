(function(){
'use strict';
var d=document;

/* Slider */
function initSlider(){
var slides=d.querySelectorAll('.hero-slide'),dots=d.querySelectorAll('.slider-dot'),p=d.querySelector('.slider-arrow--prev'),n=d.querySelector('.slider-arrow--next'),t=slides.length;
if(!t)return;
var c=0,iv;
function go(i){slides[c].classList.remove('active');dots[c]&&dots[c].classList.remove('active');c=(i+t)%t;slides[c].classList.add('active');dots[c]&&dots[c].classList.add('active')}
function nx(){go(c+1)}function pv(){go(c-1)}
function sa(){iv=setInterval(nx,4500)}function sp(){clearInterval(iv)}
p&&p.addEventListener('click',function(e){e.preventDefault();pv();sp();sa()});
n&&n.addEventListener('click',function(e){e.preventDefault();nx();sp();sa()});
dots.forEach(function(dt,i){dt.addEventListener('click',function(){go(i);sp();sa()})});
slides[0].classList.add('active');if(dots[0])dots[0].classList.add('active');sa()
}

/* Mobile Drawer */
function initDrawer(){
var h=d.getElementById('js-hamburger'),dr=d.getElementById('js-mobile-drawer'),ov=d.getElementById('js-drawer-overlay'),cl=d.getElementById('js-drawer-close');
if(!h||!dr)return;
function op(){dr.classList.add('open');ov&&ov.classList.add('visible');d.body.style.overflow='hidden'}
function clo(){dr.classList.remove('open');ov&&ov.classList.remove('visible');d.body.style.overflow=''}
h.addEventListener('click',op);cl&&cl.addEventListener('click',clo);ov&&ov.addEventListener('click',clo)
}

/* Search */
function initSearch(){
var t=d.getElementById('js-search-toggle-mob'),ov=d.getElementById('js-search-overlay');
if(!t||!ov)return;
t.addEventListener('click',function(){ov.classList.toggle('active')});
ov.addEventListener('click',function(e){if(e.target===ov)ov.classList.remove('active')})
}

/* Scroll top */
function initScrollTop(){
var b=d.getElementById('js-totop');if(!b)return;
window.addEventListener('scroll',function(){b.classList.toggle('visible',window.scrollY>400)});
b.addEventListener('click',function(){window.scrollTo({top:0,behavior:'smooth'})})
}

/* Header hide on scroll */
function initHeaderHide(){
var h=d.querySelector('.site-header');if(!h)return;
var ls=0;
window.addEventListener('scroll',function(){var y=window.scrollY;if(y>100&&y>ls)h.style.transform='translateY(-100%)';else h.style.transform='translateY(0)';ls=y})
}

/* Product carousel arrows */
function initCarousels(){
d.querySelectorAll('.product-carousel').forEach(function(car){
var tr=car.querySelector('.product-carousel__track');
var prev=car.querySelector('.carousel-arrow--prev');
var next=car.querySelector('.carousel-arrow--next');
if(!tr)return;
var step=220;
if(prev)prev.addEventListener('click',function(){tr.scrollBy({left:-step,behavior:'smooth'})});
if(next)next.addEventListener('click',function(){tr.scrollBy({left:step,behavior:'smooth'})})
})
}

d.addEventListener('DOMContentLoaded',function(){
initSlider();initDrawer();initSearch();initScrollTop();initHeaderHide();initCarousels()
})
})()
