<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>App</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white pt-16">

<!-- Navbar -->
<nav class="fixed top-0 z-50 w-full bg-gray-600 border-b border-gray-700 h-24">
  <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4 h-full">
    
    <!-- Logo -->
    <a href="#" class="flex items-center space-x-3 rtl:space-x-reverse">
        <img src="https://flowbite.com/docs/images/logo.svg" class="h-7" alt="Logo" />
        <span class="self-center text-xl text-white font-semibold whitespace-nowrap">Flowbite</span>
    </a>

    <!-- Mobile Button -->
    <button data-collapse-toggle="navbar-default" type="button"
      class="inline-flex items-center p-2 w-10 h-10 justify-center text-gray-200 rounded-base md:hidden hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-gray-400"
      aria-controls="navbar-default" aria-expanded="false">
        <span class="sr-only">Open main menu</span>
        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M5 7h14M5 12h14M5 17h14"/>
        </svg>
    </button>

    <!-- Menu -->
    <div class="hidden w-full md:block md:w-auto" id="navbar-default">
      <ul class="font-medium flex flex-col p-4 md:p-0 mt-4 border border-gray-700 rounded-base bg-gray-600 md:flex-row md:space-x-8 rtl:space-x-reverse md:mt-0 md:border-0">

        <li>
          <a href="#" class="block py-2 px-3 text-white rounded md:bg-transparent md:text-white md:p-0 hover:text-gray-300">
            User
          </a>
        </li>

      </ul>
    </div>

  </div>
</nav>

<!-- Sidebar -->
<aside id="logo-sidebar" 
   class="fixed top-0 left-0 z-40 w-72 h-screen pt-24 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0" 
   aria-label="Sidebar">

   <div class="h-full px-4 py-6 overflow-y-auto bg-neutral-primary-soft border-e border-default">

      <ul class="space-y-3 font-medium">

         <!-- Dashboard -->
         <li>
            <a href="{{ route('dashboard') }}" 
               class="flex items-center gap-4 px-4 py-3 rounded-lg text-gray-700
                      transition-all duration-200 ease-in-out
                      hover:bg-gray-100 hover:text-black hover:translate-x-1 hover:shadow-sm hover:scale-[1.02]">
                
                <svg class="w-6 h-6 transition duration-200" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M5 3a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H5Zm14 18a2 2 0 0 0 2-2v-2a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h4ZM5 11a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H5Zm14 2a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h4Z"/>
                </svg>

               <span class="text-sm font-semibold tracking-wide">Dashboard</span>
            </a>
         </li>

         <!-- Billing -->
         <li>
            <a href="#" 
               class="flex items-center gap-4 px-4 py-3 rounded-lg text-gray-700
                      transition-all duration-200 ease-in-out
                      hover:bg-gray-100 hover:text-black hover:translate-x-1 hover:shadow-sm hover:scale-[1.02]">
                
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M8 7V6a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1h-1M3 18v-7a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Zm8-3.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"/>
                </svg>

               <span class="text-sm font-semibold tracking-wide">Billing</span>
            </a>
         </li>

         <!-- Subscriber Management -->
         <li>
            <a href="#" 
               class="flex items-center gap-4 px-4 py-3 rounded-lg text-gray-700
                      transition-all duration-200 ease-in-out
                      hover:bg-gray-100 hover:text-black hover:translate-x-1 hover:shadow-sm hover:scale-[1.02]">
                
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.7 15h4.3c.4 0 .7-.4.7-.9V3.9c0-.5-.3-.9-.7-.9H6.7c-.6 0-1 .4-1 1v4m11 7v-3h3v3h-3Zm-3 6H6.7c-.6 0-1-.4-1-1 0-1.7 1.3-3 3-3h3c1.7 0 3 1.3 3 3 0 .6-.4 1-1 1Zm-1-9.5A2.5 2.5 0 1 1 10.2 9a2.5 2.5 0 0 1 2.5 2.5Z"/>
                </svg>

               <span class="text-sm font-semibold tracking-wide">Subscriber Management</span>
            </a>
         </li>

         <!-- Admin Management -->
         <li>
            <a href="#" 
               class="flex items-center gap-4 px-4 py-3 rounded-lg text-gray-700
                      transition-all duration-200 ease-in-out
                      hover:bg-gray-100 hover:text-black hover:translate-x-1 hover:shadow-sm hover:scale-[1.02]">
                
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M16 19h4a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-2m-2-4a3 3 0 1 0 0-4M3 18v-1a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Zm8-10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                </svg>

               <span class="text-sm font-semibold tracking-wide">Admin Management</span>
            </a>
         </li>

         <!-- Reports -->
         <li>
            <a href="#" 
               class="flex items-center gap-4 px-4 py-3 rounded-lg text-gray-700
                      transition-all duration-200 ease-in-out
                      hover:bg-gray-100 hover:text-black hover:translate-x-1 hover:shadow-sm hover:scale-[1.02]">
                
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M4 19V5m0 14h16M8 17v-6m4 6V7m4 10v-4"/>
                </svg>

               <span class="text-sm font-semibold tracking-wide">Reports</span>
            </a>
         </li>

         <!-- Settings -->
         <li>
            <a href="#" 
               class="flex items-center gap-4 px-4 py-3 rounded-lg text-gray-700
                      transition-all duration-200 ease-in-out
                      hover:bg-gray-100 hover:text-black hover:translate-x-1 hover:shadow-sm hover:scale-[1.02]">
                
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M10.8 5a3 3 0 0 0-5.7 0H4a1 1 0 1 0 0 2h1.2a3 3 0 0 0 5.7 0H20a1 1 0 1 0 0-2h-9.2ZM4 11h9.2a3 3 0 0 1 5.7 0H20a1 1 0 1 1 0 2h-1.2a3 3 0 0 1-5.7 0H4a1 1 0 1 1 0-2Zm1.2 6H4a1 1 0 1 0 0 2h1.2a3 3 0 0 0 5.7 0H20a1 1 0 1 0 0-2h-9.2a3 3 0 0 0-5.7 0Z"/>
                </svg>

               <span class="text-sm font-semibold tracking-wide">Settings</span>
            </a>
         </li>

         <!-- Logout -->
         <li>
            <a href="{{ route('login') }}" 
               class="flex items-center gap-4 px-4 py-3 rounded-lg text-red-600
                      transition-all duration-200 ease-in-out
                      hover:bg-red-100 hover:text-red-700 hover:translate-x-1 hover:shadow-sm hover:scale-[1.02]">
                
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H8m12 0-4 4m4-4-4-4M9 4H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h2"/>
                </svg>

               <span class="text-sm font-semibold tracking-wide">Logout</span>
            </a>
         </li>

      </ul>
   </div>
</aside>

<!-- Main Content -->
<div class="sm:ml-72 mt-24 p-4">
    <div class="p-4 border border-default border-dashed rounded-base">

        @yield('content')

    </div>
</div>

</body>
</html>