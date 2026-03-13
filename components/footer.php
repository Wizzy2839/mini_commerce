        </main>

        <!-- Bottom Navigation (Mobile Only) -->
        <nav class="md:hidden fixed bottom-0 left-0 w-full glass-panel border-t-0 shadow-[0_-4px_24px_rgba(128,0,32,0.08)] flex justify-around py-3 z-50 rounded-t-3xl">
            <a class="flex flex-col items-center gap-1.5 text-primary-600 transition-transform active:scale-95" href="/index.php">
                <div class="px-5 py-1.5 bg-primary-50 rounded-full">
                    <span class="material-symbols-outlined fill-1">home</span>
                </div>
                <span class="text-[10px] font-bold">Beranda</span>
            </a>
            <a class="flex flex-col items-center gap-1.5 text-slate-400 hover:text-primary-500 transition-all active:scale-95" href="/history.php">
                <div class="px-5 py-1.5 rounded-full hover:bg-slate-50 transition-colors">
                    <span class="material-symbols-outlined">receipt_long</span>
                </div>
                <span class="text-[10px] font-medium">Pesanan</span>
            </a>
            <a class="flex flex-col items-center gap-1.5 text-slate-400 hover:text-primary-500 transition-all active:scale-95" href="#">
                <div class="px-5 py-1.5 rounded-full hover:bg-slate-50 transition-colors">
                    <span class="material-symbols-outlined">favorite</span>
                </div>
                <span class="text-[10px] font-medium">Favorit</span>
            </a>
            <a class="flex flex-col items-center gap-1.5 text-slate-400 hover:text-primary-500 transition-all active:scale-95" href="/profile.php">
                <div class="px-5 py-1.5 rounded-full hover:bg-slate-50 transition-colors">
                    <span class="material-symbols-outlined">person</span>
                </div>
                <span class="text-[10px] font-medium">Profil</span>
            </a>
        </nav>

        <!-- Premium Footer -->
        <footer class="relative bg-white border-t border-slate-100 pt-16 pb-8 px-4 lg:px-8 mt-20 hidden md:block overflow-hidden">
            <!-- Decorative bg pattern -->
            <div class="absolute inset-0 opacity-[0.02] pointer-events-none" style="background-image: radial-gradient(#800020 1px, transparent 1px); background-size: 24px 24px;"></div>
            
            <div class="max-w-7xl mx-auto grid grid-cols-4 gap-12 relative z-10">
                <div class="col-span-1 pr-6">
                    <div class="flex items-center gap-2.5 text-primary-800 mb-6 group cursor-pointer inline-flex">
                        <div class="size-10 bg-primary-50 rounded-2xl flex items-center justify-center text-primary-700 group-hover:bg-primary-600 group-hover:text-white transition-all duration-300">
                            <span class="material-symbols-outlined">shopping_bag</span>
                        </div>
                        <h2 class="text-2xl font-extrabold tracking-tight text-accent">Shop<span class="text-primary-600">Ease</span></h2>
                    </div>
                    <p class="text-slate-500 text-sm leading-relaxed mb-6">Platform e-commerce premium. Belanja gaya hidup modern dengan pengalaman tak terlupakan.</p>
                </div>
                
                <div>
                    <h5 class="text-sm font-bold text-accent uppercase tracking-widest mb-5">Layanan</h5>
                    <ul class="space-y-3 text-sm text-slate-500 font-medium">
                        <li><a class="hover:text-primary-600 hover:translate-x-1 inline-block transition-all" href="#">Pusat Bantuan</a></li>
                        <li><a class="hover:text-primary-600 hover:translate-x-1 inline-block transition-all" href="#">Metode Pembayaran</a></li>
                        <li><a class="hover:text-primary-600 hover:translate-x-1 inline-block transition-all" href="#">Lacak Pesanan</a></li>
                        <li><a class="hover:text-primary-600 hover:translate-x-1 inline-block transition-all" href="#">Pengembalian Dana</a></li>
                    </ul>
                </div>
                
                <div>
                    <h5 class="text-sm font-bold text-accent uppercase tracking-widest mb-5">Perusahaan</h5>
                    <ul class="space-y-3 text-sm text-slate-500 font-medium">
                        <li><a class="hover:text-primary-600 hover:translate-x-1 inline-block transition-all" href="#">Tentang Kami</a></li>
                        <li><a class="hover:text-primary-600 hover:translate-x-1 inline-block transition-all" href="#">Karier & Talenta</a></li>
                        <li><a class="hover:text-primary-600 hover:translate-x-1 inline-block transition-all" href="#">Kebijakan Privasi</a></li>
                        <li><a class="hover:text-primary-600 hover:translate-x-1 inline-block transition-all" href="#">Syarat & Ketentuan</a></li>
                    </ul>
                </div>
                
                <div>
                    <h5 class="text-sm font-bold text-accent uppercase tracking-widest mb-5">Tetap Terhubung</h5>
                    <div class="flex gap-3 mb-6">
                        <a href="#" class="size-10 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 hover:bg-primary-600 hover:text-white hover:-translate-y-1 hover:shadow-glow transition-all duration-300">
                            <span class="material-symbols-outlined text-[20px]">social_leaderboard</span>
                        </a>
                        <a href="#" class="size-10 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 hover:bg-primary-600 hover:text-white hover:-translate-y-1 hover:shadow-glow transition-all duration-300">
                            <span class="material-symbols-outlined text-[20px]">camera</span>
                        </a>
                        <a href="#" class="size-10 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 hover:bg-primary-600 hover:text-white hover:-translate-y-1 hover:shadow-glow transition-all duration-300">
                            <span class="material-symbols-outlined text-[20px]">alternate_email</span>
                        </a>
                    </div>
                    <p class="text-xs text-slate-400">Ikuti sosial media kami untuk info promo terbaru.</p>
                </div>
            </div>
            
            <div class="max-w-7xl mx-auto mt-12 pt-8 border-t border-slate-100 text-center relative z-10 flex flex-col md:flex-row items-center justify-between gap-4">
                <span class="text-slate-400 text-sm font-medium">© 2024 ShopEase Inc. Hak Cipta Dilindungi.</span>
                <div class="flex gap-2">
                    <span class="px-2 py-1 bg-slate-50 text-slate-400 text-xs rounded font-medium border border-slate-200">IDR</span>
                    <span class="px-2 py-1 bg-slate-50 text-slate-400 text-xs rounded font-medium border border-slate-200">Indonesia</span>
                </div>
            </div>
        </footer>
    </div>
</div>
</body>
</html>
