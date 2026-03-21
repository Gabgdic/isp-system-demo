  <!DOCTYPE html>
  <html lang="en">
  <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Client Area</title>

  <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="antialiased bg-gradient-to-t from-gray-300 via-gray-100 to-white bg-no-repeat">
    <div class="container mx-auto px-6">

      <div class="flex flex-col md:flex-row h-screen justify-evenly md:items-center text-center md:text-left">

        <div class="flex flex-col w-full items-center md:items-start">

          <div>
            <svg
              class="w-20 h-20 mx-auto md:float-left text-gray-700"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"
              ></path>
            </svg>
          </div>

          <h1 class="text-5xl font-bold text-gray-800">
            Client Area
          </h1>

          <p class="max-w-md mx-auto md:mx-0 text-gray-500">
            Control and monitorize your website data from dashboard.
          </p>

        </div>

        <div class="w-full md:w-full lg:w-9/12 mx-auto flex justify-center">

          <div class="bg-white p-10 flex flex-col w-full max-w-md shadow-xl rounded-xl">

            <h2 class="text-2xl font-bold text-gray-800 text-center mb-5">
              Sign In
            </h2>

            <form method="POST" action="{{ route('login.post') }}" class="w-full">
                @csrf

                <div class="flex flex-col w-full my-5">
                    <label for="username" class="text-gray-600 mb-2">
                        Username
                    </label>

                    <input
                        type="text"
                        name="username"
                        id="username"
                        placeholder="Please insert your username"
                        class="appearance-none border-2 border-gray-200 rounded-lg px-4 py-3 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:shadow-lg"
                    />
                </div>

                <div class="flex flex-col w-full my-5">
                    <label for="password" class="text-gray-600 mb-2">
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="Please insert your password"
                        class="appearance-none border-2 border-gray-200 rounded-lg px-4 py-3 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:shadow-lg"
                    />
                </div>

                <div class="flex flex-col w-full my-5">

                    <button
                        type="submit"
                        class="w-full py-4 bg-gray-800 rounded-lg text-white hover:bg-gray-700 transition"
                    >
                        <div class="flex flex-row items-center justify-center">

                            <div class="mr-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"
                                    ></path>
                                </svg>
                            </div>

                            <div class="font-bold">
                                Sign In
                            </div>

                        </div>
                    </button>

                </div>
            </form> 
          </div>
        </div>

      </div>
    </div>
  </body>
  </html>