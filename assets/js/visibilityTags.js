import axios from 'axios';

let switchs = document.querySelectorAll('[data-switch-active-tag]');

if (switchs) {
    switchs.forEach((element) => {
        element.addEventListener('change', () => {
            let tagId = element.value;
            axios.get(`/admin/categorie/switch/${tagId}`);
        });
    });
}