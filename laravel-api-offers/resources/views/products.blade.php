<!DOCTYPE html>
<html>
<head>
    <title>Products</title>
</head>
<body>

<h1>RCA Products</h1>

<button onclick="loadProducts()">Load products</button>

<pre id="output"></pre>

<script>
    function loadProducts() {
        fetch('/product')
            .then(res => res.json())
            .then(data => {
                document.getElementById('output').textContent =
                    JSON.stringify(data, null, 2);
            });
    }
</script>

</body>
</html>
