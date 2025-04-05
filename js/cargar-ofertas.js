document.addEventListener('DOMContentLoaded', function() {
    fetch('admin/datos.json')
        .then(response => response.json())
        .then(data => {
            // Cargar ofertas en el modal
            const modalBody = document.querySelector('.modal-body');
            let offersHTML = '';
            
            if (data.ofertas && data.ofertas.length > 0) {
                offersHTML += '<h3>Ofertas Especiales</h3>';
                data.ofertas.forEach(oferta => {
                    offersHTML += `
                        <div class="offer-card">
                            ${oferta.imagen ? `<img src="${oferta.imagen}" alt="${oferta.titulo}" class="offer-image">` : ''}
                            <div class="offer-content">
                                ${oferta.etiqueta ? `<span class="offer-tag">${oferta.etiqueta}</span>` : ''}
                                <h3>${oferta.titulo}</h3>
                                <p>${oferta.descripcion}</p>
                            </div>
                        </div>
                    `;
                });
            }
            
            // Cargar novedades
            if (data.novedades && data.novedades.length > 0) {
                offersHTML += '<h3 style="margin-top: 30px;">Novedades</h3>';
                data.novedades.forEach(novedad => {
                    offersHTML += `
                        <div class="news-item">
                            <h4>${novedad.titulo}</h4>
                            <p>${novedad.descripcion}</p>
                        </div>
                    `;
                });
            }
            
            modalBody.innerHTML = offersHTML || '<p>No hay ofertas o novedades en este momento.</p>';
        })
        .catch(error => {
            console.error('Error cargando ofertas:', error);
            document.querySelector('.modal-body').innerHTML = 
                '<p>No se pudieron cargar las ofertas. Por favor intente más tarde.</p>';
        });
});