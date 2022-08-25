import axios from 'axios';

let switchs = document.querySelectorAll('[data-switch-active-post]');

if (switchs) {
    switchs.forEach((element) => {
        element.addEventListener('change', () => {
            let postId = element.value;
            axios.get(`/admin/article/switch/${postId}`);
        });
    });
}