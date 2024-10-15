# Peach CAPTCHA


# Story
I’ve been living on my own for quite some time now, which means I don’t get to see my beautiful cat as often as I’d like. His name is Peach, and he means the world to me. He lives at my parents’ house, so this CAPTCHA is a small way to keep him close, even when we’re apart. In honor of him, I decided to name this CAPTCHA “Peach CAPTCHA.”
# How to Solve the CAPTCHA
To solve the CAPTCHA, simply outline Peach, my beloved cat, in the image—don’t forget the tail! Once you’ve traced his whole body, click the “Validate CAPTCHA” button to see if you’ve passed.

![ezgif com-video-to-gif-converter](https://github.com/user-attachments/assets/2aff629c-4443-4b74-8a8c-c5c1b0bcba58)

# Building and Installing
You will need [Git](https://git-scm.com) and [Docker](https://www.docker.com) already installed on your system. Your system also has to be able to execute `make -v` command
### 1. Clone the Repository
```shell
git clone https://github.com/DewFill/peach-captcha-php.git
```

### 2. Start the Application
This command will build a Docker image, run all containers and install Composer
```shell
make start
```
The application will run on http://localhost:80.
### Additional Usage
```shell
# Stop all containers without losing data
make stop
```
```shell
# Delete all containers, volumes, and networks
make clear
```
```shell
# Build the image
make build-image
```
```shell
# Open a Bash shell in the PHP container
make bash
```

# Endpoints
API endpoints are not listed here. You can see all available endpoints in the [HttpHandler](http/HttpHandler.php) class in the `registerStandardHandlers` method
* http://localhost:80 - Generates new CAPTCHA. You have ~40 seconds to solve it.
* http://localhost:80/mask - Creates a new CAPTCHA.
* http://localhost:80/mask?mask_id={id} - Edits an existing CAPTCHA.


# Security
The security of this application has not been tested.

Anyone can edit the CAPTCHA settings, as the admin endpoints do not require authentication.

# Testing
This application was manually tested on Apple Silicon macOS computer
* Unit Tests are not available
* End-to-end Tests are not available
* Behavioral Tests are not available

# Troubleshooting
**Bind for 0.0.0.0:80 failed: port is already allocated** 

If port 80 is already in use, you can easily override it. Simply create a deployment/docker-compose.override.yml file and copy the contents from deployment/docker-compose.override.yml.example into it. After that, start the application as usual.

# Accessibility
This CAPTCHA requires the ability to visually identify and outline objects in an image, which may not be accessible to individuals who are visually impaired, people with cognitive or motor impairments, or users relying on screen readers. Additionally, slow internet connections may affect the loading and solving process. Consider using alternative CAPTCHA methods, such as audio or text-based challenges, to ensure broader accessibility.

# Suggestions
* Take a look at [LaurieWired](https://www.youtube.com/@lauriewired/videos) on YouTube. I especially enjoyed the videos "[What ACTUALLY happens during a Stack Overflow?](https://www.youtube.com/watch?v=_6zAAhkU_Iw)" and "[How a Clever 1960s Memory Trick Changed Computing](https://www.youtube.com/watch?v=vc79sJ9VOqk)".
* Check out [BroskiReport](https://www.youtube.com/@BroskiReport). She's fun, and I enjoy listening to her podcast in the background.
* Stream [Brat](https://charlixcx.lnk.to/bratcompletelydifferentID) by Charli XCX.
* Check out [my Telegram channel](http://t.me/phpgarbage). I occasionally post educational content about PHP.
