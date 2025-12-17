import { Config } from 'ziggy-js';

const Ziggy: Config = {
    url: 'http://localhost',
    port: 8000,
    defaults: {},
    routes: {
        'login': { uri: 'login', methods: ['GET', 'HEAD'] },
        'logout': { uri: 'logout', methods: ['POST'] },
        'dashboard': { uri: 'dashboard', methods: ['GET', 'HEAD'] },
        'annealing-checks.index': { uri: 'annealing-checks', methods: ['GET', 'HEAD'] },
        'annealing-checks.create': { uri: 'annealing-checks/create', methods: ['GET', 'HEAD'] },
        'annealing-checks.store': { uri: 'annealing-checks', methods: ['POST'] },
        'annealing-checks.show': { uri: 'annealing-checks/{annealing_check}', methods: ['GET', 'HEAD'] },
        'annealing-checks.edit': { uri: 'annealing-checks/{annealing_check}/edit', methods: ['GET', 'HEAD'] },
        'annealing-checks.update': { uri: 'annealing-checks/{annealing_check}', methods: ['PUT', 'PATCH'] },
        'annealing-checks.destroy': { uri: 'annealing-checks/{annealing_check}', methods: ['DELETE'] },
    }
};

export default Ziggy;
