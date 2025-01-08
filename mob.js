function toggleNavbarPosition() {
    const navbar = document.querySelector('.navbar');

    if (window.innerWidth < 700) {
        navbar.classList.remove('fixed-top');
        navbar.classList.add('fixed-bottom');
    } else {
        navbar.classList.remove('fixed-bottom');
        navbar.classList.add('fixed-top');
    }
}

window.addEventListener('load', () => {
    toggleNavbarPosition();
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        navbar.classList.add('visible');
    }

    adjustNavbar(); 
});

window.addEventListener('resize', () => {
    toggleNavbarPosition();
    adjustNavbar();
});

function adjustNavbar() {
    const logo = document.querySelector('.navbar-brand');
    const searchBar = document.querySelector('#search'); 

    if (window.innerWidth <= 700) {
        if (logo) logo.style.display = 'none'; 
    } else {
        if (logo) logo.style.display = 'block';
    }
}



