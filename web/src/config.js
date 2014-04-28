/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Configure Mustache
Mustache.tags = ['[', ']'];

// Configure Dependency Injection Container
container = new Flyspeck();

container.set('scroll', (c) => {
    return new Scroll($('#chat'));
});

container.set('sound', (c) => {
    return new Sound();
});

container.set('users', (c) => {
    return new Users();
});

container.set('emotion', (c) => {
    return new Emotion();
});

container.set('app', (c) => {
    return new Application(c.get('server'));
});