<style>
    .bottom-menu {
        position: fixed;
        bottom: 60px; 
        left: 50%;
        transform: translateX(-50%);
        width: 35%; 
        background-color: #54a854;
        display: flex;
        justify-content: space-around;
        padding: 10px;
        border-radius: 12px; 
        z-index: 1000;
        height: 55px;
        transition: transform 0.3s, opacity 0.3s;
    }
    .bottom-menu.hidden {
        transform: translateX(-50%) translateY(100px);
        opacity: 0;
    }
    .bottom-menu a {
        color: white;
        text-decoration: none;
        font-size: 14px;
        text-align: center;
        flex: 1;
    }
    .bottom-menu a i {
        display: block;
        font-size: 20px;
        margin-bottom: 2px;
    }
    .bottom-menu a:hover {
        background-color: #9dff96;
    }
    .hidden {
    opacity: 0;
    transition: opacity 0.3s;
    pointer-events: none;
}

    @media (max-width: 568px) {
        .bottom-menu {
            width: 100%;
            bottom: 8px;
            padding: 8px;
        }
        .bottom-menu a i {
            font-size: 18px;
        }
        .bottom-menu a {
            font-size: 12px;
        }
    }
</style>

<div class="bottom-menu" id="bottomMenu">
    <a href="dashboard.php">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
    </a>
    <a href="sell.php">
        <i class="fas fa-dollar-sign"></i>
        <span>Sell</span>
    </a>
    <a href="donate.php">
        <i class="fas fa-hand-holding-heart"></i>
        <span>Donate</span>
    </a>
</div>

<!-- JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
    const bottomMenu = document.getElementById('bottomMenu');
    const footer = document.getElementById('student_footer'); 
    let lastScrollY = window.scrollY;

    if (footer) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    bottomMenu.classList.add('hidden'); 
                }
            });
        }, { threshold: 0.1 });

        observer.observe(footer);
    }

    window.addEventListener('scroll', () => {
        if (window.scrollY < lastScrollY) {
            bottomMenu.classList.remove('hidden');
        } else {
            if (!footer || !footer.getBoundingClientRect().top <= window.innerHeight) {
                bottomMenu.classList.add('hidden');
            }
        }
        lastScrollY = window.scrollY;
    });
});

</script>
