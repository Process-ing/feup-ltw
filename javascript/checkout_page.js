"use strict";
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var _a;
function createOrderItemCard(product) {
    return __awaiter(this, void 0, void 0, function* () {
        const orderItemCard = document.createElement('div');
        orderItemCard.classList.add('order-item-card');
        const image = document.createElement('img');
        image.src = (yield getProductImages(product.id))[0];
        image.alt = 'Product Image';
        orderItemCard.appendChild(image);
        image.addEventListener('click', (event) => goToProduct(product.id));
        const itemInfo = document.createElement('div');
        orderItemCard.appendChild(itemInfo);
        const itemTitle = document.createElement('h1');
        itemTitle.innerHTML = product.title;
        itemInfo.appendChild(itemTitle);
        const itemDetails = document.createElement('p');
        const itemDetailsText = [product.size, product.condition].filter(detail => detail).join(' - ');
        if (itemDetailsText !== '') {
            itemDetails.innerHTML = itemDetailsText;
            itemInfo.appendChild(itemDetails);
        }
        const itemPrice = document.createElement('p');
        itemPrice.classList.add('price');
        itemPrice.innerHTML = `${product.price}`;
        orderItemCard.appendChild(itemPrice);
        return orderItemCard;
    });
}
function updateTotal(checkoutSubtotal, checkoutShipping, checkoutTotal, shippingInput, subtotal, shipping) {
    checkoutSubtotal.innerHTML = subtotal.toFixed(2);
    if (shipping >= 0) {
        checkoutShipping.innerHTML = shipping.toFixed(2);
        checkoutShipping.classList.add('price');
        checkoutTotal.innerHTML = (subtotal + shipping).toFixed(2);
        checkoutTotal.classList.add('price');
        shippingInput.value = shipping.toFixed(2);
    }
    else {
        checkoutShipping.innerHTML = checkoutTotal.innerHTML = '-';
        checkoutShipping.classList.remove('price');
        checkoutTotal.classList.remove('price');
        shippingInput.value = '0.00';
    }
}
function getShippingCost(checkoutForm) {
    return __awaiter(this, void 0, void 0, function* () {
        const formData = convertToObject(new FormData(checkoutForm));
        if (formData.address && formData.zip && formData.town && formData.country) {
            return getData(`/actions/action_shipping.php?address=${formData.address}&zip=${formData.zip}&town=${formData.town}&country=${formData.country}`)
                .then(response => response.json())
                .then(json => {
                if (json.success) {
                    return json.shipping;
                }
                else {
                    sendToastMessage('An unexpected error occurred', 'error');
                    console.error(json.error);
                    return -1;
                }
            })
                .catch(error => {
                sendToastMessage('An unexpected error occurred', 'error');
                console.error(error);
                return -1;
            });
        }
        else {
            return -1;
        }
    });
}
;
function submitCheckoutForm(checkoutForm) {
    return __awaiter(this, void 0, void 0, function* () {
        return postData(checkoutForm.action, convertToObject(new FormData(checkoutForm)))
            .then(response => response.json());
    });
}
const orderItemsSection = document.querySelector('#order-items');
const payNowButton = document.querySelector('#pay-now-button');
const checkoutInfoForm = document.querySelector('#checkout-info-form');
const checkoutSubtotal = document.querySelector('#checkout-subtotal');
const checkoutShipping = document.querySelector('#checkout-shipping');
const checkoutTotal = document.querySelector('#checkout-total');
const shippingInput = (_a = checkoutInfoForm === null || checkoutInfoForm === void 0 ? void 0 : checkoutInfoForm.querySelector('input[name="shipping"]')) !== null && _a !== void 0 ? _a : null;
let subtotal = 0;
if (orderItemsSection && payNowButton && checkoutInfoForm && checkoutSubtotal && checkoutShipping && checkoutTotal && shippingInput) {
    getCart()
        .then((json) => __awaiter(void 0, void 0, void 0, function* () {
        if (json.success) {
            const cart = json.cart;
            for (const product of cart) {
                const orderItemCard = createOrderItemCard(product);
                orderItemsSection.appendChild(yield orderItemCard);
                subtotal += product.price;
            }
            if (checkoutSubtotal && checkoutShipping && checkoutTotal)
                updateTotal(checkoutSubtotal, checkoutShipping, checkoutTotal, shippingInput, subtotal, -1);
        }
        else {
            sendToastMessage('Could not get cart, try again later', 'error');
            console.error(json.error);
        }
    }))
        .catch((error) => {
        sendToastMessage('An unexpected error occurred', 'error');
        console.error(error);
    });
    const formInputs = checkoutInfoForm.querySelectorAll('input');
    formInputs.forEach(formInput => {
        formInput.addEventListener('blur', () => {
            getShippingCost(checkoutInfoForm)
                .then(shipping => updateTotal(checkoutSubtotal, checkoutShipping, checkoutTotal, shippingInput, subtotal, shipping));
        });
    });
    payNowButton.addEventListener('click', () => {
        if (!checkoutInfoForm.checkValidity()) {
            checkoutInfoForm.reportValidity();
            return;
        }
        const loadingSpinner = document.createElement('div');
        loadingSpinner.classList.add('spinner');
        loadingSpinner.appendChild(document.createElement('div'));
        payNowButton.replaceWith(loadingSpinner);
        window.setTimeout(() => __awaiter(void 0, void 0, void 0, function* () {
            loadingSpinner.replaceWith(payNowButton);
            submitCheckoutForm(checkoutInfoForm)
                .then((json) => __awaiter(void 0, void 0, void 0, function* () {
                if (json.success) {
                    payNowButton.disabled = true;
                    yield sendToastMessage('Payment successful!', 'success');
                    document.location.assign('/');
                }
                else {
                    sendToastMessage('Could not checkout, try again later', 'error');
                    console.error(json.error);
                }
            }))
                .catch((error) => {
                sendToastMessage('An unexpected error occurred', 'error');
                console.error(error);
            });
        }), 2000);
    });
}
