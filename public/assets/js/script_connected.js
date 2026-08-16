    const map = L.map('map').setView([48.866667, 2.333333], 18); 
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
    }).addTo(map);
    
    let vendeurMarker;

    map.on('click', function (e) {
        if (vendeurMarker) map.removeLayer(vendeurMarker);
        vendeurMarker = L.marker(e.latlng).addTo(map);
        document.getElementById('latitude').value = e.latlng.lat.toFixed(6);
        document.getElementById('longitude').value = e.latlng.lng.toFixed(6);
    });

    const livraisonToggle = document.getElementById('livraisonActive');
    const livraisonDetails = document.getElementById('livraisonDetails');
    livraisonToggle.addEventListener('change', () => {
        livraisonDetails.style.display = livraisonToggle.checked ? 'block' : 'none';
    });

    function distanceKm(lat1, lon1, lat2, lon2) {
        const R = 6371; 
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    document.getElementById('adresseClient').addEventListener('change', () => {
        const latV = parseFloat(document.getElementById('latitude').value);
        const lonV = parseFloat(document.getElementById('longitude').value);
        const latC = latV + Math.random() * 0.05; 
        const lonC = lonV + Math.random() * 0.05;
        const dist = distanceKm(latV, lonV, latC, lonC);
        const tarif = parseFloat(document.getElementById('tarifKm').value);
        const total = (dist * tarif).toFixed(2);
        document.getElementById('distanceResult').innerHTML = `
            Distance : ${dist.toFixed(2)} km<br>
            Coût total livraison : ${total} Euro
        `;
    });

//
// Ajouter l'animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);

    document.body.appendChild(alert);

    setTimeout(() => {
        alert.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => {
            alert.remove();
        }, 300);
    }, 3000);



newPassword.addEventListener('input', () => {
    const length = newPassword.value.length;
    if (length > 0 && length < 12) {
        newPassword.style.borderColor = '#f44336';
    } else if (length >= 12) {
        newPassword.style.borderColor = '#4CAF50';
    } else {
        newPassword.style.borderColor = '#ddd';
    }
});

confirmPassword.addEventListener('input', () => {
    if (confirmPassword.value && confirmPassword.value !== newPassword.value) {
        confirmPassword.style.borderColor = '#f44336';
    } else if (confirmPassword.value === newPassword.value && confirmPassword.value.length >= 12) {
        confirmPassword.style.borderColor = '#4CAF50';
    } else {
        confirmPassword.style.borderColor = '#ddd';
    }
});

// Mes annonces 
// mes_annonces.js

document.addEventListener('DOMContentLoaded', function() {
    // Initialisation
    initializeSearch();
    initializeFilters();
    initializeActions();
    initializePagination();
});

// RECHERCHE
function initializeSearch() {
    const searchInput = document.querySelector('.search-input');
    const searchBtn = document.querySelector('.btn-search');
    
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            performSearch();
        });
    }
    
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }
}

function performSearch() {
    const searchInput = document.querySelector('.search-input');
    const filterSelect = document.querySelector('.filter-select');
    const searchTerm = searchInput.value.toLowerCase();
    const category = filterSelect.value;
    
    const annonceCards = document.querySelectorAll('.annonce-card');
    
    annonceCards.forEach(card => {
        const title = card.querySelector('.annonce-title').textContent.toLowerCase();
        const tags = Array.from(card.querySelectorAll('.tag')).map(tag => 
            tag.textContent.toLowerCase()
        );
        
        const matchesSearch = title.includes(searchTerm);
        const matchesCategory = category === 'all' || tags.some(tag => tag.includes(category));
        
        if (matchesSearch && matchesCategory) {
            card.style.display = 'flex';
            // Animation d'apparition
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.4s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 50);
        } else {
            card.style.display = 'none';
        }
    });
}

// FILTRES
function initializeFilters() {
    const filterSelect = document.querySelector('.filter-select');
    
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            performSearch();
        });
    }
}

// ACTIONS SUR LES ANNONCES
function initializeActions() {
    // Boutons iCal
    const icalButtons = document.querySelectorAll('.btn-ical');
    icalButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const card = this.closest('.annonce-card');
            const title = card.querySelector('.annonce-title').textContent;
            handleIcalDownload(title);
        });
    });
    
    // Boutons Modifier
    const editButtons = document.querySelectorAll('.btn-edit');
    editButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const card = this.closest('.annonce-card');
            const title = card.querySelector('.annonce-title').textContent;
            handleEdit(title);
        });
    });
    
    // Boutons Supprimer
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const card = this.closest('.annonce-card');
            const title = card.querySelector('.annonce-title').textContent;
            handleDelete(card, title);
        });
    });
}

function handleIcalDownload(title) {
    console.log(`Téléchargement iCal pour: ${title}`);
    // Animation de confirmation
    showNotification('Fichier iCal généré avec succès', 'success');
    
    // Ici vous pouvez ajouter la logique pour générer et télécharger le fichier iCal
    // Par exemple: window.location.href = `/download-ical/${annonceId}`;
}

function handleEdit(title) {
    console.log(`Modification de l'annonce: ${title}`);
    // Redirection vers la page d'édition
    // window.location.href = `/annonces/edit/${annonceId}`;
    showNotification('Redirection vers l\'éditeur...', 'info');
}

function handleDelete(card, title) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer l'annonce "${title}" ?`)) {
        // Animation de suppression
        card.style.transition = 'all 0.4s ease';
        card.style.opacity = '0';
        card.style.transform = 'translateX(-100%)';
        
        setTimeout(() => {
            card.remove();
            showNotification('Annonce supprimée avec succès', 'success');
            updateAnnonceCount();
        }, 400);
        
        // Ici vous pouvez ajouter la logique AJAX pour supprimer du serveur
        // fetch(`/annonces/delete/${annonceId}`, { method: 'DELETE' })
    }
}

// PAGINATION
function initializePagination() {
    const pageButtons = document.querySelectorAll('.page-btn');
    
    pageButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            if (!this.classList.contains('active') && !this.classList.contains('page-next')) {
                // Retirer active de tous les boutons
                pageButtons.forEach(b => b.classList.remove('active'));
                // Ajouter active au bouton cliqué
                this.classList.add('active');
                
                // Charger la page
                const pageNumber = this.textContent;
                loadPage(pageNumber);
            } else if (this.classList.contains('page-next')) {
                // Aller à la page suivante
                const currentActive = document.querySelector('.page-btn.active');
                const currentPage = parseInt(currentActive.textContent);
                const nextPage = currentPage + 1;
                
                if (nextPage <= 2) { // Maximum 2 pages dans cet exemple
                    loadPage(nextPage);
                }
            }
        });
    });
}

function loadPage(pageNumber) {
    console.log(`Chargement de la page ${pageNumber}`);
    
    // Animation de chargement
    const annoncesList = document.querySelector('.annonces-list');
    annoncesList.style.opacity = '0.5';
    
    setTimeout(() => {
        annoncesList.style.opacity = '1';
        showNotification(`Page ${pageNumber} chargée`, 'info');
        
        // Faire défiler vers le haut
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }, 300);
    
    // Ici vous pouvez ajouter la logique AJAX pour charger les annonces
    // fetch(`/annonces?page=${pageNumber}`)
}

// NOTIFICATIONS
function showNotification(message, type = 'info') {
    // Créer l'élément de notification
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Styles
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.padding = '16px 24px';
    notification.style.borderRadius = '8px';
    notification.style.color = '#fff';
    notification.style.fontWeight = '500';
    notification.style.zIndex = '9999';
    notification.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
    notification.style.transform = 'translateX(400px)';
    notification.style.transition = 'transform 0.3s ease';
    
    // Couleurs selon le type
    switch(type) {
        case 'success':
            notification.style.background = '#10b981';
            break;
        case 'error':
            notification.style.background = '#ef4444';
            break;
        case 'info':
            notification.style.background = '#3b82f6';
            break;
        default:
            notification.style.background = '#666';
    }
    
    document.body.appendChild(notification);
    
    // Animation d'entrée
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // Retirer après 3 secondes
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// MISE À JOUR DU COMPTEUR D'ANNONCES
function updateAnnonceCount() {
    const remainingCards = document.querySelectorAll('.annonce-card').length;
    const sectionTitle = document.querySelector('.section-title');
    
    if (remainingCards === 0) {
        // Afficher un message si plus d'annonces
        const emptyMessage = document.createElement('div');
        emptyMessage.className = 'empty-state';
        emptyMessage.innerHTML = `
            <div style="text-align: center; padding: 60px 20px; color: #999;">
                <i class="fa-solid fa-inbox" style="font-size: 64px; margin-bottom: 20px;"></i>
                <h3 style="color: #666; margin-bottom: 10px;">Aucune annonce active</h3>
                <p>Commencez par créer votre première annonce</p>
            </div>
        `;
        
        const annoncesList = document.querySelector('.annonces-list');
        annoncesList.innerHTML = '';
        annoncesList.appendChild(emptyMessage);
    } else {
        sectionTitle.textContent = `Annonces actives (${remainingCards})`;
    }
}

// Animation au scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '0';
            entry.target.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                entry.target.style.transition = 'all 0.6s ease';
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }, 100);
            
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

// Observer les cartes
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.annonce-card');
    cards.forEach((card, index) => {
        card.style.transitionDelay = `${index * 0.1}s`;
        observer.observe(card);
    });
});
