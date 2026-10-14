</main>

<footer class="bg-white border-t mt-auto">
  <div class="max-w-6xl mx-auto px-4 py-6 text-center text-sm text-gray-500">
    &copy; <?php echo date('Y'); ?> FreshMart Grocery Store. All rights reserved.
  </div>
</footer>

<script src="/assets/js/script.js"></script>
<script>
  document.getElementById('mobile-menu-btn')?.addEventListener('click', function () {
    document.getElementById('nav-links-mobile').classList.toggle('hidden');
    document.getElementById('nav-links-mobile').classList.toggle('flex');
  });
</script>
</body>
</html>
