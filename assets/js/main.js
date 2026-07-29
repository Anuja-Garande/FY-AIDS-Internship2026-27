document.addEventListener('DOMContentLoaded', function() {
    
    // Department Filter Logic
    const filterChips = document.querySelectorAll('.filter-chip');
    const doctorCards = document.querySelectorAll('.doctor-card-wrapper');

    filterChips.forEach(chip => {
        chip.addEventListener('click', function() {
            filterChips.forEach(c => c.classList.remove('active'));
            this.classList.add('active');

            const selectedDept = this.getAttribute('data-dept');

            doctorCards.forEach(card => {
                const cardDept = card.getAttribute('data-dept-id');
                if (selectedDept === 'all' || cardDept === selectedDept) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // Leaflet Interactive Map Setup (Updated to Pune, India)
    const mapContainer = document.getElementById('hospitalMap');
    if (mapContainer) {
        // Pune Coordinates: 18.5204, 73.8567
        const puneLat = 18.5204;
        const puneLng = 73.8567;

        // Initialize Leaflet Map centered on Pune
        const map = L.map('hospitalMap', {
            center: [puneLat, puneLng],
            zoom: 14,
            zoomControl: true
        });

        // Add Dark Matter Tile Layer (Matching Glassmorphism Theme)
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 19
        }).addTo(map);

        // Custom Glowing Pulse Marker Icon
        const customIcon = L.divIcon({
            className: 'custom-map-marker',
            html: `
                <div style="
                    width: 36px;
                    height: 36px;
                    background: linear-gradient(135deg, #00f2fe, #4facfe);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #fff;
                    font-size: 1.1rem;
                    box-shadow: 0 0 20px rgba(0, 242, 254, 0.8);
                    border: 2px solid #ffffff;
                ">
                    <i class="fa-solid fa-hospital"></i>
                </div>
            `,
            iconSize: [36, 36],
            iconAnchor: [18, 18]
        });

        // Add Marker & Popup for ApexCare Pune
        const marker = L.marker([puneLat, puneLng], { icon: customIcon }).addTo(map);

        const popupContent = `
            <div style="padding: 0.25rem; font-family: system-ui, -apple-system, sans-serif;">
                <strong style="color: #00f2fe; font-size: 0.95rem; display: block; margin-bottom: 0.25rem;">ApexCare Super Specialty Hospital</strong>
                <span style="font-size: 0.82rem; color: #333; display: block; margin-bottom: 0.4rem;">742 FC Road, Shivajinagar, Pune, Maharashtra 411005</span>
                <span style="font-size: 0.78rem; background: rgba(0,230,118,0.15); color: #00a850; padding: 2px 8px; border-radius: 12px; font-weight: 600; display: inline-block;">
                    <i class="fa-solid fa-circle" style="font-size: 0.5rem; vertical-align: middle;"></i> Emergency 24/7 Open
                </span>
            </div>
        `;

        marker.bindPopup(popupContent).openPopup();
    }
});