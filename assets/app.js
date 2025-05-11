import './styles/app.scss';

import * as bootstrap from 'bootstrap';

import { BookForm } from './js/modules/bookForm.js';
import { BookSearch } from './js/modules/bookSearch.js';
import { BookList } from './js/modules/bookList.js';

document.addEventListener('DOMContentLoaded', () => {
    BookForm.init();
    
    BookSearch.init();
    
    BookList.init();
});