function showPrice() {
    const prices = {
        '35k-long': '35,000 UGX – Awesome quality Long Sleeve',
        '25k-short': '25,000 UGX – Awesome quality Short Sleeve',
        '15k-short': '15,000 UGX – Nice quality Short Sleeve'
    };
    const selected = document.getElementById('quality').value;
    document.getElementById('display-price').innerText = 'Price: ' + prices[selected];
}
