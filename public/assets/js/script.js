// Sidebar
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const closeSidebar = document.getElementById('closeSidebar');
menuToggle.onclick = () => sidebar.classList.add('active');
closeSidebar.onclick = () => sidebar.classList.remove('active');

// Mobile search
const searchToggle = document.getElementById('searchToggle');
const mobileSearch = document.getElementById('mobileSearch');
searchToggle.onclick = () => {
    mobileSearch.classList.toggle('active');
};

//Signup and login
// Sélection des éléments
const modal = document.getElementById('authModal');
const loginTab = document.querySelector('.tab-btn[data-tab="login"]');
const registerTab = document.querySelector('.tab-btn[data-tab="register"]');
const loginContent = document.getElementById('login');
const registerContent = document.getElementById('register');
const closeModal = document.getElementById('closeModal');
const loginIcon = document.querySelector('.header-right .fa-right-to-bracket');

if (loginIcon && loginIcon.parentElement) {
  // Ouvrir modal sur clic bouton header
  document.querySelector('.header-right .fa-right-to-bracket').parentElement.addEventListener('click', () => {
      modal.style.display = 'block';
  });
}
// Fermer modal
closeModal.addEventListener('click', () => {
    modal.style.display = 'none';
});

// Changer d’onglet
loginTab.addEventListener('click', () => {
    loginTab.classList.add('active');
    registerTab.classList.remove('active');
    loginContent.style.display = 'block';
    registerContent.style.display = 'none';
});

registerTab.addEventListener('click', () => {
    registerTab.classList.add('active');
    loginTab.classList.remove('active');
    registerContent.style.display = 'block';
    loginContent.style.display = 'none';
});

// Fermer modal en cliquant à l’extérieur
window.addEventListener('click', (e) => {
    if (e.target === modal) modal.style.display = 'none';
});



// ========== CAROUSEL CATEGORIES ==========
document.addEventListener('DOMContentLoaded', function() {
    const categoriesSection = document.querySelector('.categories-section');
    if (!categoriesSection) return;

    const track = categoriesSection.querySelector('.carousel-track');
    const cards = categoriesSection.querySelectorAll('.category-card');
    const prevBtn = categoriesSection.querySelector('.prev-btn');
    const nextBtn = categoriesSection.querySelector('.next-btn');
    const dotsContainer = categoriesSection.querySelector('.carousel-dots');
    
    let currentIndex = 0;
    let cardsPerView = 5;

    function updateCardsPerView() {
        const width = window.innerWidth;
        if (width < 576) cardsPerView = 1;
        else if (width < 768) cardsPerView = 2;
        else if (width < 992) cardsPerView = 3;
        else if (width < 1200) cardsPerView = 4;
        else cardsPerView = 5;
    }

    function createDots() {
        const totalDots = Math.ceil(cards.length / cardsPerView);
        dotsContainer.innerHTML = '';
        
        for (let i = 0; i < totalDots; i++) {
            const dot = document.createElement('div');
            dot.classList.add('dot');
            if (i === 0) dot.classList.add('active');
            dot.addEventListener('click', () => goToSlide(i * cardsPerView));
            dotsContainer.appendChild(dot);
        }
    }

    function goToSlide(index) {
        const maxIndex = cards.length - cardsPerView;
        currentIndex = Math.max(0, Math.min(index, maxIndex));
        
        const cardWidth = cards[0].offsetWidth;
        const gap = 25;
        const offset = -(currentIndex * (cardWidth + gap));
        
        track.style.transform = `translateX(${offset}px)`;
        
        const currentDot = Math.floor(currentIndex / cardsPerView);
        dotsContainer.querySelectorAll('.dot').forEach((dot, i) => {
            dot.classList.toggle('active', i === currentDot);
        });

        prevBtn.disabled = currentIndex === 0;
        nextBtn.disabled = currentIndex >= maxIndex;
    }

    prevBtn.addEventListener('click', () => {
        goToSlide(currentIndex - cardsPerView);
    });

    nextBtn.addEventListener('click', () => {
        goToSlide(currentIndex + cardsPerView);
    });

    // Touch events for mobile
    let startX = 0;
    let isDragging = false;

    track.addEventListener('touchstart', (e) => {
        startX = e.touches[0].clientX;
        isDragging = true;
    });

    track.addEventListener('touchend', (e) => {
        if (!isDragging) return;
        isDragging = false;
        
        const endX = e.changedTouches[0].clientX;
        const diff = startX - endX;
        
        if (Math.abs(diff) > 50) {
            if (diff > 0) {
                goToSlide(currentIndex + cardsPerView);
            } else {
                goToSlide(currentIndex - cardsPerView);
            }
        }
    });

    updateCardsPerView();
    createDots();
    goToSlide(0);

    window.addEventListener('resize', () => {
        updateCardsPerView();
        createDots();
        goToSlide(0);
    });
});


// ========== CAROUSEL ANNONCES ==========
document.addEventListener('DOMContentLoaded', function() {
    const annoncesSection = document.querySelector('.annonces-section');
    if (!annoncesSection) return;

    const track = annoncesSection.querySelector('.carousel-track');
    const cards = annoncesSection.querySelectorAll('.annonce-card');
    const prevBtn = annoncesSection.querySelector('.carousel-btn.prev-btn');
    const nextBtn = annoncesSection.querySelector('.carousel-btn.next-btn');
    const dotsContainer = annoncesSection.querySelector('.carousel-dots');
    
    let currentIndex = 0;
    let cardsPerView = 3;

    function updateCardsPerView() {
        const width = window.innerWidth;
        if (width < 576) cardsPerView = 1;
        else if (width < 992) cardsPerView = 2;
        else cardsPerView = 3;
    }

    function createDots() {
        const totalPages = Math.ceil(cards.length / cardsPerView);
        dotsContainer.innerHTML = '';
        
        for (let i = 0; i < totalPages; i++) {
            const dot = document.createElement('button');
            dot.classList.add('dot');
            if (i === 0) dot.classList.add('active');
            dot.addEventListener('click', () => goToSlide(i * cardsPerView));
            dotsContainer.appendChild(dot);
        }
    }

    function goToSlide(index) {
        const maxIndex = cards.length - cardsPerView;
        currentIndex = Math.max(0, Math.min(index, maxIndex));
        
        const cardWidth = cards[0].offsetWidth;
        const gap = 30;
        const offset = -(currentIndex * (cardWidth + gap));
        
        track.style.transform = `translateX(${offset}px)`;
        
        const currentDot = Math.floor(currentIndex / cardsPerView);
        dotsContainer.querySelectorAll('.dot').forEach((dot, i) => {
            dot.classList.toggle('active', i === currentDot);
        });

        prevBtn.disabled = currentIndex === 0;
        nextBtn.disabled = currentIndex >= maxIndex;
    }

    prevBtn.addEventListener('click', () => {
        goToSlide(currentIndex - cardsPerView);
    });

    nextBtn.addEventListener('click', () => {
        goToSlide(currentIndex + cardsPerView);
    });

    // Touch events for mobile
    let startX = 0;
    let isDragging = false;

    track.addEventListener('touchstart', (e) => {
        startX = e.touches[0].clientX;
        isDragging = true;
    });

    track.addEventListener('touchend', (e) => {
        if (!isDragging) return;
        isDragging = false;
        
        const endX = e.changedTouches[0].clientX;
        const diff = startX - endX;
        
        if (Math.abs(diff) > 50) {
            if (diff > 0) {
                goToSlide(currentIndex + cardsPerView);
            } else {
                goToSlide(currentIndex - cardsPerView);
            }
        }
    });

    updateCardsPerView();
    createDots();
    goToSlide(0);

    window.addEventListener('resize', () => {
        updateCardsPerView();
        createDots();
        goToSlide(0);
    });
});

//form creer un site
document.addEventListener('DOMContentLoaded', function() {
  const siteType = document.getElementById('siteType');
  const budget = document.getElementById('budget');
  if (!siteType || !budget) return;
  siteType.addEventListener('change', function() {
    switch (this.value) {
      case 'Blog':
        budget.value = 'À partir de 500 €';
        break;
      case 'Site vitrine':
        budget.value = 'À partir de 1500 €';
        break;
      case 'E-commerce':
        budget.value = 'À partir de 3000 €';
        break;
      default:
        budget.value = 'Sélectionnez un type de site';
    }
  });
});

//Gallery image 
  let currentSlide = 0;
        const slides = document.querySelectorAll('.gallery-slide');
        const totalSlides = slides.length;

        // Créer les indicateurs
        const indicatorsContainer = document.getElementById('indicators');
        for (let i = 0; i < totalSlides; i++) {
            const indicator = document.createElement('div');
            indicator.className = 'indicator';
            if (i === 0) indicator.classList.add('active');
            indicator.onclick = () => goToSlide(i);
            indicatorsContainer.appendChild(indicator);
        }

        function changeSlide(direction) {
            currentSlide += direction;
            if (currentSlide < 0) currentSlide = totalSlides - 1;
            if (currentSlide >= totalSlides) currentSlide = 0;
            updateGallery();
        }

        function goToSlide(index) {
            currentSlide = index;
            updateGallery();
        }

        function updateGallery() {
            const slidesContainer = document.getElementById('gallerySlides');
            if (!slidesContainer) return;
            slidesContainer.style.transform = `translateX(-${currentSlide * 100}%)`;
            
            // Mettre à jour les indicateurs
            const indicators = document.querySelectorAll('.indicator');
            indicators.forEach((indicator, index) => {
                indicator.classList.toggle('active', index === currentSlide);
            });
        }

        // Auto-play (optionnel)
        setInterval(() => {
            changeSlide(1);
        }, 5000);

        // Observer pour mettre à jour l'onglet actif lors du scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = entry.target.getAttribute('id');
                    const tabs = document.querySelectorAll('.tab-link');
                    tabs.forEach(tab => {
                        tab.classList.remove('active');
                        if (tab.getAttribute('href') === `#${id}`) {
                            tab.classList.add('active');
                        }
                    });
                }
            });
        }, {
            threshold: 0.5
        });

        // Observer toutes les sections
        document.querySelectorAll('.content-section').forEach(section => {
            observer.observe(section);
        });

//Category 
// ============================================
// INITIALIZATION
// ============================================
document.addEventListener('DOMContentLoaded', function() {
  initializeFilters();
  initializeViewToggle();
  initializePagination();
  initializeMap();
  initializeCategories();
  initializeCardClicks();
});

// ============================================
// FILTERS SIDEBAR TOGGLE
// ============================================
function initializeFilters() {
  const toggleBtn = document.getElementById('toggleFilters');
  const sidebar = document.getElementById('filtersSidebar');
  const filterText = document.getElementById('filterText');
  
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', function() {
      sidebar.classList.toggle('hidden');
      
      if (sidebar.classList.contains('hidden')) {
        filterText.textContent = 'Afficher les filtres';
      } else {
        filterText.textContent = 'Masquer les filtres';
      }
    });
  }
  
  // Price Range Filter
  const priceRange = document.querySelector('.range-slider');
  if (priceRange) {
    priceRange.addEventListener('input', function(e) {
      const value = e.target.value;
      const max = e.target.max;
      const percentage = (value / max) * 100;
      
      // Update visual feedback if needed
      console.log('Prix max:', value);
    });
  }
  
  // Filter Button
  const filterBtn = document.querySelector('.filter-btn');
  if (filterBtn) {
    filterBtn.addEventListener('click', function() {
      console.log('Filtres de prix activés');
      this.textContent = 'Filtres appliqués ✓';
      this.style.background = 'var(--primary-orange)';
      this.style.color = 'white';
      
      setTimeout(() => {
        this.textContent = 'Activer Filtrer les prix';
        this.style.background = '';
        this.style.color = '';
      }, 2000);
    });
  }
  
  // Search input
  const searchInputs = document.querySelectorAll('.search-input');
  searchInputs.forEach(input => {
    input.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        console.log('Recherche:', this.value);
        // Ajouter la logique de recherche ici
      }
    });
  });
  
  // Category select
  const categorySelect = document.querySelector('.select-input');
  if (categorySelect) {
    categorySelect.addEventListener('change', function() {
      console.log('Catégorie sélectionnée:', this.value);
      // Ajouter la logique de filtrage ici
    });
  }
}

// ============================================
// VIEW TOGGLE (GRID / LIST)
// ============================================
function initializeViewToggle() {
  const viewBtns = document.querySelectorAll('.view-btn');
  const gridView = document.getElementById('listingsGrid');
  const listView = document.getElementById('listingsList');
  
  if (!viewBtns.length || !gridView || !listView) return;
  
  viewBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const view = this.dataset.view;
      
      // Update active button
      viewBtns.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      
      // Toggle views
      if (view === 'grid') {
        gridView.style.display = 'grid';
        listView.style.display = 'none';
      } else {
        gridView.style.display = 'none';
        listView.style.display = 'flex';
        
        // Clone cards to list view if empty
        if (listView.children.length === 0) {
          const cards = gridView.querySelectorAll('.annonce-card');
          cards.forEach(card => {
            const clonedCard = card.cloneNode(true);
            listView.appendChild(clonedCard);
          });
          initializeCardClicks();
        }
      }
    });
  });
}

// ============================================
// FAVORITES
// ============================================
document.addEventListener('click', async function(e) {
    const btn = e.target.closest('.favorite-btn');
    if (!btn) return;

    e.stopPropagation();
    e.preventDefault();

    const icon = btn.querySelector('i');
    const adId = btn.dataset.adId;

    try {
        const response = await fetch(`/ads/${adId}/favorite`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (response.status === 401) {
            // Non authentifié -> ouvrir le modal login
            const modal = document.getElementById('authModal');
            if (modal) modal.style.display = 'block';
            // Activer l'onglet login
            const loginTab = document.querySelector('.tab-btn[data-tab="login"]');
            const registerTab = document.querySelector('.tab-btn[data-tab="register"]');
            const loginContent = document.getElementById('login');
            const registerContent = document.getElementById('register');
            if (loginTab && registerTab && loginContent && registerContent) {
                loginTab.classList.add('active');
                registerTab.classList.remove('active');
                loginContent.style.display = 'block';
                registerContent.style.display = 'none';
            }
            return;
        } else if (data.status === 'added') {
            icon.classList.remove('far');
            icon.classList.add('fas');
            btn.classList.add('active');
        } else {
            icon.classList.remove('fas');
            icon.classList.add('far');
            btn.classList.remove('active');
        }

        btn.dataset.favorited = data.status === 'added' ? 'true' : 'false';

        btn.style.transform = 'scale(1.2)';
        setTimeout(() => btn.style.transform = '', 200);

    } catch(err) {
        console.error('Erreur favoris:', err);
    }
});
document.addEventListener('click', async function(e) {
    const btn = e.target.closest('.action-btn');
    if (!btn) return;

    e.stopPropagation();
    e.preventDefault();

    const icon = btn.querySelector('i');
    const adId = btn.dataset.adId;

    try {
        const response = await fetch(`/ads/${adId}/favorite`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (response.status === 401) {
            // Non authentifié -> ouvrir le modal login
            const modal = document.getElementById('authModal');
            if (modal) modal.style.display = 'block';
            // Activer l'onglet login
            const loginTab = document.querySelector('.tab-btn[data-tab="login"]');
            const registerTab = document.querySelector('.tab-btn[data-tab="register"]');
            const loginContent = document.getElementById('login');
            const registerContent = document.getElementById('register');
            if (loginTab && registerTab && loginContent && registerContent) {
                loginTab.classList.add('active');
                registerTab.classList.remove('active');
                loginContent.style.display = 'block';
                registerContent.style.display = 'none';
            }
            return;
        } else if (data.status === 'added') {
            icon.classList.remove('far');
            icon.classList.add('fas');
            btn.classList.add('active');
        } else {
            icon.classList.remove('fas');
            icon.classList.add('far');
            btn.classList.remove('active');
        }

        btn.dataset.favorited = data.status === 'added' ? 'true' : 'false';

        btn.style.transform = 'scale(1.2)';
        setTimeout(() => btn.style.transform = '', 200);

    } catch(err) {
        console.error('Erreur favoris:', err);
    }
});
// ============================================
// PAGINATION
// ============================================
function initializePagination() {
  const prevBtn = document.getElementById('prevPage');
  const nextBtn = document.getElementById('nextPage');
  const pageNumbersContainer = document.getElementById('pageNumbers');
  
  if (!prevBtn || !nextBtn || !pageNumbersContainer) return;
  
  let currentPage = 1;
  const totalPages = 10; // Nombre total de pages
  
  function renderPageNumbers() {
    pageNumbersContainer.innerHTML = '';
    
    // Logic pour afficher les numéros de page (max 5 visibles)
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, startPage + 4);
    
    if (endPage - startPage < 4) {
      startPage = Math.max(1, endPage - 4);
    }
    
    // Première page
    if (startPage > 1) {
      addPageButton(1);
      if (startPage > 2) {
        addEllipsis();
      }
    }
    
    // Pages du milieu
    for (let i = startPage; i <= endPage; i++) {
      addPageButton(i);
    }
    
    // Dernière page
    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        addEllipsis();
      }
      addPageButton(totalPages);
    }
  }
  
  function addPageButton(pageNum) {
    const pageBtn = document.createElement('button');
    pageBtn.className = 'page-number';
    pageBtn.textContent = pageNum;
    
    if (pageNum === currentPage) {
      pageBtn.classList.add('active');
    }
    
    pageBtn.addEventListener('click', function() {
      currentPage = pageNum;
      updatePagination();
    });
    
    pageNumbersContainer.appendChild(pageBtn);
  }
  
  function addEllipsis() {
    const ellipsis = document.createElement('span');
    ellipsis.textContent = '...';
    ellipsis.style.padding = '0 8px';
    ellipsis.style.color = 'var(--text-gray)';
    pageNumbersContainer.appendChild(ellipsis);
  }
  
  function updatePagination() {
    renderPageNumbers();
    
    prevBtn.disabled = currentPage === 1;
    nextBtn.disabled = currentPage === totalPages;
    
    // Scroll to top smooth
    const listingsContainer = document.querySelector('.listings-container');
    if (listingsContainer) {
      listingsContainer.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    console.log('Page actuelle:', currentPage);
  }
  
  prevBtn.addEventListener('click', function() {
    if (currentPage > 1) {
      currentPage--;
      updatePagination();
    }
  });
  
  nextBtn.addEventListener('click', function() {
    if (currentPage < totalPages) {
      currentPage++;
      updatePagination();
    }
  });
  
  // Initial render
  renderPageNumbers();
  updatePagination();
}

// ============================================
// MAP INITIALIZATION
// ============================================
function initializeMap() {
  const mapElement = document.getElementById('map');
  
  if (!mapElement || typeof L === 'undefined') {
    console.warn('Leaflet non chargé ou élément map introuvable');
    return;
  }
  
  // Coordonnées de Paris
  const map = L.map('map', {
    zoomControl: false,
    scrollWheelZoom: true
  }).setView([48.8566, 2.3522], 13);
  
  // Tile layer OpenStreetMap
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap',
    maxZoom: 19
  }).addTo(map);
  
  // Données des annonces avec coordonnées
  const locations = [
    { lat: 48.8566, lng: 2.3522, title: 'PS5', price: '€15,00', category: 'Location console de jeux' },
    { lat: 48.8606, lng: 2.3376, title: 'Clio', price: '€60,00', category: 'Location véhicule' },
    { lat: 48.8529, lng: 2.3499, title: 'Renault clio 2', price: '€40,00', category: 'Location voiture' },
    { lat: 48.8584, lng: 2.2945, title: 'Voiture Rouge', price: '€1 500,00', category: 'Location véhicule' },
    { lat: 48.8738, lng: 2.2950, title: 'Karcher vapeur', price: '€30,00', category: 'Outils' },
    { lat: 48.8414, lng: 2.2699, title: 'Aspirateur', price: '€40,00', category: 'Location maison' },
    { lat: 48.8500, lng: 2.3200, title: 'Scène professionnelle', price: '€300,00', category: 'Événementiel' }
  ];
  
  // Icône personnalisée
  const customIcon = L.icon({
    iconUrl: 'data:image/svg+xml;base64,' + btoa(`
      <svg width="32" height="40" xmlns="http://www.w3.org/2000/svg">
        <path d="M16 0C9.373 0 4 5.373 4 12c0 9 12 28 12 28s12-19 12-28c0-6.627-5.373-12-12-12z" fill="#FF4D1C"/>
        <circle cx="16" cy="12" r="4" fill="white"/>
      </svg>
    `),
    iconSize: [32, 40],
    iconAnchor: [16, 40],
    popupAnchor: [0, -40]
  });
  
  // Ajouter les marqueurs
  locations.forEach(location => {
    const marker = L.marker([location.lat, location.lng], { icon: customIcon })
      .addTo(map)
      .bindPopup(`
        <div style="font-family: sans-serif; min-width: 150px;">
          <strong style="color: #2d3436; font-size: 14px;">${location.title}</strong><br>
          <span style="color: #636e72; font-size: 12px;">${location.category}</span><br>
          <span style="color: #FF4D1C; font-weight: 600; font-size: 13px; margin-top: 4px; display: inline-block;">Commence à partir de ${location.price}</span>
        </div>
      `);
  });
  
  // Custom zoom controls
  const zoomInBtn = document.getElementById('zoom-in');
  const zoomOutBtn = document.getElementById('zoom-out');
  const locateBtn = document.getElementById('locate');
  
  if (zoomInBtn) {
    zoomInBtn.addEventListener('click', () => map.zoomIn());
  }
  
  if (zoomOutBtn) {
    zoomOutBtn.addEventListener('click', () => map.zoomOut());
  }
  
  if (locateBtn) {
    locateBtn.addEventListener('click', () => {
      map.locate({ setView: true, maxZoom: 16 });
    });
    
    map.on('locationfound', function(e) {
      L.marker(e.latlng, { icon: customIcon })
        .addTo(map)
        .bindPopup('📍 Vous êtes ici!')
        .openPopup();
    });
    
    map.on('locationerror', function() {
      alert('Impossible de vous localiser');
    });
  }
}

// ============================================
// CATEGORIES
// ============================================
function initializeCategories() {
  const categoryItems = document.querySelectorAll('.category-item');
  
  categoryItems.forEach(item => {
    item.addEventListener('click', function() {
      const categoryName = this.querySelector('.category-name').textContent;
      
      // Remove active from all
      categoryItems.forEach(i => i.classList.remove('active'));
      
      // Add active to clicked
      this.classList.add('active');
      
      console.log('Catégorie sélectionnée:', categoryName);
      
      // Ici vous pouvez ajouter la logique pour filtrer les annonces
    });
  });
}

// ============================================
// CARD CLICKS
// ============================================
function initializeCardClicks() {
  const cards = document.querySelectorAll('.annonce-card');
  
  cards.forEach(card => {
    // Remove existing click listener
    const newCard = card.cloneNode(true);
    card.parentNode.replaceChild(newCard, card);
    
    newCard.addEventListener('click', function(e) {
      // Don't trigger if clicking favorite button
      if (e.target.closest('.favorite-btn')) {
        return;
      }
      
      const title = this.querySelector('.card-title-text').textContent;
      console.log('Annonce cliquée:', title);
      
      // Redirection vers page détail (décommenter en production)
      // window.location.href = '/annonce/detail'; 
    });
  });
}

// ============================================
// SORT DROPDOWN
// ============================================
const sortDropdown = document.querySelector('.sort-dropdown');
if (sortDropdown) {
  sortDropdown.addEventListener('click', function() {
    console.log('Menu de tri - à implémenter');
    // Ajouter un menu dropdown ici
  });
}