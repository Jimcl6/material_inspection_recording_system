import { Config, RouteParam, RouteParamsWithQueryOverload, Router as ZiggyRouter } from 'ziggy-js';

declare global {
    interface Window {
        route: typeof route;
        Ziggy: Config;
    }
}

declare function route(
    name?: string,
    params?: RouteParamsWithQueryOverload | RouteParam,
    absolute?: boolean,
    customZiggy?: Config
): string;

declare function route(
    name: undefined,
    params?: RouteParamsWithQueryOverload | RouteParam,
    absolute?: boolean,
    customZiggy?: Config
): typeof ZiggyRouter;

export {};
