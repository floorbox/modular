import fs from "fs/promises";
import path from "path";
import { pathToFileURL } from "url";

async function collectModuleAssetsPaths(paths, modulesPath) {
    modulesPath = path.join(modulesPath, "app-modules");

    try {
        // Read module directories
        const moduleDirectories = await fs.readdir(modulesPath);

        for (const moduleDir of moduleDirectories) {
            if (moduleDir === ".DS_Store" || moduleDir === ".gitignore") {
                // Skip .DS_Store directory
                continue;
            }
            try {
                const jsPath = path.join(modulesPath, moduleDir, "resources/js");
                const jsFiles = await fs.readdir(jsPath);
                for (const jsFile of jsFiles) {
                    if (jsFile === ".DS_Store" || jsFile === ".gitignore") {
                        // Skip .DS_Store directory
                        continue;
                    }
                    // Check if the file exists
                    try {
                        await fs.access(path.join(jsPath, jsFile));
                        const finalJsPath = jsPath.split("app-modules")[1];
                        paths.push(path.join("app-modules", finalJsPath, jsFile));
                    } catch (err) {}
                }
            } catch (err) {}

            try {
                const cssPath = path.join(modulesPath, moduleDir, "resources/css");
                const cssFiles = await fs.readdir(cssPath);
                for (const cssFile of cssFiles) {
                    if (cssFile === ".DS_Store" || cssFile === ".gitignore") {
                        // Skip .DS_Store directory
                        continue;
                    }
                    try {
                        await fs.access(path.join(cssPath, cssFile));
                        const finalCssPath = cssPath.split("app-modules")[1];
                        paths.push(path.join("app-modules", finalCssPath, cssFile));
                    } catch (err) {}
                }
            } catch (err) {}
        }
    } catch (error) {
        console.error(`Error reading module statuses or module configurations: ${error}`);
    }

    return paths;
}

export default collectModuleAssetsPaths;
