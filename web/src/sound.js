/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Sound {
    constructor() {
        this.message = this.create(window.config.sound.message);
        this.join = this.create(window.config.sound.join);
    }

    create(file) {
        return new Howl({
            urls: [file]
        });
    }
}

