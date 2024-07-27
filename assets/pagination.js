import './styles/components/pagination.css';

// const paginateForm = document.querySelector("form.items-per-page");
//
// paginateForm.addEventListener("change", function (event) {
//   const xhr = new XMLHttpRequest();
//   const page = paginateForm.page.value;
//   let url = window.location.href;
//   if ('URLSearchParams' in window) {
//     let searchParams = new URLSearchParams(window.location.search);
//     searchParams.set('page', page);
//     searchParams.set("itemsPerPage", event.target.value);
//
//     url = url + '?' + searchParams.toString();
//   }
//   xhr.open("GET", url, true);
//
//   // function execute after request is successful
//   xhr.onreadystatechange = function () {
//       if (this.readyState === 4 && this.status === 200) {
//           console.log(this.responseText);
//       }
//   }
//   // Sending our request
//   xhr.send();
// });

console.log('This log comes from assets/pagination.js - welcome to AssetMapper! 🎉')
